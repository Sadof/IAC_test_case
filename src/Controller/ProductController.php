<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use App\Repository\ProductRepository;
use App\Service\ProductService;
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
    public function editProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ProductService $product_service
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('product_edit', $request->get("csrf_token"))) {
            throw $this->createNotFoundException('The CSRF token is invalid. Please try to resubmit the form');
        }

        $product_data = $request->request->all();
        $files = $request->files;

        if ($product_data["id"] && $product_data["id"] != "null") {
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
                $product = $product_service->fillProduct($product_data, $files, $product);
            } catch (TypeError $e) {
                $logger->error($e, ["user" => $this->getUser()]);
                return $this->response("Ошибка создания продукта", 404);
            } catch (Exception $e) {
                $logger->error($e, ["user" => $this->getUser()]);
                return $this->response("Ошибка создания продукта", 404);
            }
            

            $entityManager->flush();
            return $this->response("Продукт изменен", 200);
        } else {
            if (!in_array('ROLE_ADD', $this->getUser()->getRoles(), true)) {
                throw $this->createAccessDeniedException('Недостаточно прав для создания продукта');
            }
            try {
                $product = $product_service->fillProduct($product_data, $files);
            } catch (TypeError $e) {
                $logger->error($e, ["user" => $this->getUser()]);
                return $this->response("Ошибка создания продукта", 404);
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

    #[Route('/api/get_data', name: 'api_data_get')]
    #[IsGranted('ROLE_EDIT')]
    public function getProduct(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = $entityManager->getRepository(Product::class)->find($data["product_id"]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for'
            );
        }

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


        $product = $entityManager->getRepository(Product::class)->find($data["product_id"]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for'
            );
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
}
