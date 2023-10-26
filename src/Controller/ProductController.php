<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;

class ProductController extends AbstractController
{
    #[Route('/', name: 'product')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', []);
    }

    #[Route('/product/create', name: 'product_create')]
    #[IsGranted('ROLE_ADD')]
    public function create(): Response
    {
        return $this->render('product/create.html.twig');
    }

    #[Route('/product/edit/{product_id}', name: 'product_edit')]
    #[IsGranted('ROLE_EDIT')]
    public function edit($product_id): Response
    {
        return $this->render('product/edit.html.twig', ["product_id" => $product_id]);
    }

    #[Route('/api/data_edit', name: 'api_data_edit')]
    public function editProduct(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->isCsrfTokenValid('product_edit', $data["csrf"])) {
        }

        $product_data = $data["product"];
        if ($product_data["id"]) {
            if (!in_array('ROLE_EDIT', $this->getUser()->getRoles(), true)) {
                throw $this->createAccessDeniedException('Недостаточно прав для редактирования продукта');
            }
            $product = $entityManager->getRepository(Product::class)->find($product_data["id"]);

            if (!$product) {
                throw $this->createNotFoundException(
                    'No product found for'
                );
            }
            try {
                $product = $this->fillProduct($product_data, $entityManager, $product);
            } catch (TypeError $e) {
                $logger->error($e, ["user" => $this->getUser()]);
                return $this->response("Ошибка создания продукта", 404);
            }

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                return $this->response($errorsString, 404);
            }

            $entityManager->flush();

            return $this->response("Продукт изменен", 200);
        } else {
            if (!in_array('ROLE_ADD', $this->getUser()->getRoles(), true)) {
                throw $this->createAccessDeniedException('Недостаточно прав для создания продукта');
            }
            try {
                $product = $this->fillProduct($product_data, $entityManager);
            } catch (TypeError $e) {
                $logger->error($e, ["user" => $this->getUser()]);
                return $this->response("Ошибка создания продукта", 404);
            }

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                return $this->response($errorsString, 404);
            }
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->response("Продукт создан", 200);
        }
    }

    #[Route('/api/data_list', name: 'api_data_list')]
    #[IsGranted('ROLE_LIST_VIEW')]
    public function getProducts(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $response = $entityManager->getRepository(Product::class)->getData($request);
        $max_entities = count($response);
        $data = $serializer->serialize(["data" => $response, "max_entities" => $max_entities, "per_page" => ProductRepository::PAGINATOR_PER_PAGE], 'json');
        return $this->response($data);
    }

    #[Route('/api/get_data', name: 'api_data_пуе')]
    #[IsGranted('ROLE_EDIT')]
    public function getProduct(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = $entityManager->getRepository(Product::class)->find($data["product_id"]);

        $data = $serializer->serialize($product, 'json');
        return $this->response($data);
    }

    #[Route('/api/delete_data', name: 'api_delete_data')]
    #[IsGranted('ROLE_DELETE')]
    public function deleteProduct(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->isCsrfTokenValid('product_delete', $data["csrf"])) {
            throw $this->createNotFoundException('The CSRF token is invalid. Please try to resubmit the form');
        }

        try {
            $product = $entityManager->getRepository(Product::class)->find($data["product_id"]);
        } catch (Exception $e) {
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->response("Deleted");
    }

    #[Route('/api/data_list_additional_data', name: 'api_data_list_additional_data')]
    #[IsGranted('ROLE_USER')]
    public function getAdditionalData(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = [];
        $data["product_color"] = $entityManager->getRepository(ProductColor::class)->findAll();
        $data["product_category"] = $entityManager->getRepository(ProductCategory::class)->getLowestLevel();
        $data["user_roles"] = $this->getUser()->getRoles();
        $data = $serializer->serialize($data, 'json');
        return $this->response($data);
    }


    private function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    private function fillProduct(array $product_data, EntityManagerInterface $entityManager,  Product $product = null): Product
    {
        if (!$product) {
            $product = new Product();
        }

        $product->setShortDescription($product_data["short_description"]);
        $product->setDescription($product_data["description"]);
        $product->setAmount($product_data["amount"]);
        $product->setWeight($product_data["weight"]);
        $product->setAddedToStore(date_create($product_data["added_to_store"]));
        $product->setUpdated(\DateTime::createFromFormat("Y-m-d H:i:s", $product_data["updated"]));
        $product->setProductCategory($product_data["product_category"] ? $entityManager->getReference(ProductCategory::class, $product_data["product_category"]) : null);
        $product->setProductColor($product_data["product_color"] ? $entityManager->getReference(ProductColor::class, $product_data["product_color"]) : null);
        $product->setImage($product_data["image"]);
        $product->setBlob($product_data["blob"]);
        return $product;
    }
}
