<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', []);
    }

    #[Route('/api/data_list', name: 'api_data_list')]
    #[IsGranted('ROLE_LIST_VIEW')]
    public function getProducts(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $response = $entityManager->getRepository(Product::class)->getData($request);
        $max_entities = count($response);
        $data = $serializer->serialize(["data" => $response, "max_entities" => $max_entities, "per_page" => ProductRepository::PAGINATOR_PER_PAGE], 'json');
        return $this->response($data);
    }
    #[Route('/api/data_list_additional_data', name: 'api_data_list_additional_data')]
    public function getAdditionalData(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $data = [];
        $data["product_color"] = $entityManager->getRepository(ProductColor::class)->findAll();
        $data["product_category"] = $entityManager->getRepository(ProductCategory::class)->getLowestLevel();
        $data["user_roles"] = $this->getUser()->getRoles();
        $data = $serializer->serialize($data, 'json');
        return $this->response($data);
    }


    public function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }
}
