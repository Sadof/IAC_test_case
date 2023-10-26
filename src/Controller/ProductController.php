<?php

namespace App\Controller;


use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        LoggerInterface $logger,
        ProductService $product_service
    ): JsonResponse {

        if (!$this->isCsrfTokenValid('product_edit', $request->request->get('csrf_token'))) {
            throw $this->createNotFoundException('The CSRF token is invalid. Please try to resubmit the form');
        }

        if (!empty($request->request->get("id"))) {
            if (!in_array('ROLE_EDIT', $this->getUser()->getRoles(), true)) {
                throw $this->createAccessDeniedException('Недостаточно прав для редактирования продукта');
            }
        } else {
            if (!in_array('ROLE_ADD', $this->getUser()->getRoles(), true)) {
                throw $this->createAccessDeniedException('Недостаточно прав для редактирования продукта');
            }
        }

        try {
            $response = $product_service->fillProduct($request);
        } catch (TypeError $e) {
            $logger->error($e, ["user" => $this->getUser()]);
            return $this->response("Ошибка создания продукта", 404);
        } catch (Exception $e) {
            $logger->error($e, ["user" => $this->getUser()]);
            return $this->response("Ошибка создания продукта", 404);
        }

        return $this->response($response, 200);
    }

    #[Route('/api/data_list', name: 'api_data_list')]
    #[IsGranted('ROLE_LIST_VIEW')]
    public function getProducts(Request $request, ProductService $product_service): JsonResponse
    {
        $data = $product_service->getProducts($request);
        return $this->response($data);
    }

    #[Route('/api/get_data', name: 'api_data_get')]
    #[IsGranted('ROLE_EDIT')]
    public function getProduct(Request $request, ProductService $product_service, LoggerInterface $logger): JsonResponse
    {
        try {
            $data = $product_service->getProduct($request);
        } catch (Exception $e) {
            $logger->error($e, ["user" => $this->getUser()]);
            return $this->response("Ошибка создания продукта", 404);
        }

        return $this->response($data);
    }

    #[Route('/api/delete_data', name: 'api_delete_data')]
    #[IsGranted('ROLE_DELETE')]
    public function deleteProduct(Request $request, ProductService $product_service, LoggerInterface $logger): JsonResponse
    {

        if (!$this->isCsrfTokenValid('product_delete', $request->request->get('csrf_token'))) {
            throw $this->createNotFoundException('The CSRF token is invalid. Please try to resubmit the form');
        }

        try {
            $product_service->deleteProduct($request);
        } catch (Exception $e) {
            $logger->error($e, ["user" => $this->getUser()]);
            return $this->response("Ошибка создания продукта", 404);
        }

        return $this->response("Deleted");
    }

    #[Route('/api/data_list_additional_data', name: 'api_data_list_additional_data')]
    #[IsGranted('ROLE_USER')]
    public function getAdditionalData(ProductService $product_service): JsonResponse
    {
        $data = $product_service->getAdditionalData();

        return $this->response($data);
    }

    private function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    #[Route('/api/get_data_blob/{id}', name: 'api_get_data_blob')]
    #[IsGranted('ROLE_EDIT')]
    public function getDataBlob(int $id, ProductService $product_service, LoggerInterface $logger)
    {
        try {
            $product = $product_service->getDataBlob($id);
        } catch (Exception $e) {
            $logger->error($e, ["user" => $this->getUser()]);
            return $this->response("Ошибка создания продукта", 404);
        }
        
        return new \Symfony\Component\HttpFoundation\Response(
            stream_get_contents($product->getBlob()),
            200,
            array(
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $product->getBlobName() .'"',
            )
        );
    }
}
