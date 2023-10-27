<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class ProductService
{
    private $entityManager;
    private $serializer;
    private $validator;
    private $security;

    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->security = $security;
    }

    /**
     * В зависимости от наличия в запросе id продукта, выбирается создание нового или обновление имеющегося продукта.
     *  
     *
     * @param Request $request
     * @return string
     */
    public function createUpdateProduct($request): string
    {
        $product_data = $request->request->all();
        $files = $request->files;
        $mode = "create";
        if ($product_data["id"]) {
            $mode = "update";
            $product = $this->entityManager->getRepository(Product::class)->find($product_data["id"]);

            if (!$product) {
                throw $this->createNotFoundException(
                    'No product found for'
                );
            }
        } else {
            $product = new Product();
        }

        $product = $this->fillProduct($product, $product_data);

        if ($files->get("image")) {
            $image_check = new Assert\File(["extensions" =>  ['jpg', 'jpeg', 'png'], "maxSize" => "2M"]);
            $errors = $this->validator->validate($files->get("image"), $image_check);

            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new Exception($errorsString);
            }

            $file_name = $this->upload($files->get("image"));
            $product->setImage($file_name);
        }

        if ($files->get("blob") && $files->get("blob")->getError() == 0) {
            $product = $this->setBlob($product, $files->get("blob"));
        }


        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new Exception($errorsString);
        }

        if ($mode == "create") {
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        return $mode == "update" ? "Продукт изменен" : "Продукт создан";
    }

    /**
     * Для переданного файла создается уникальное название на основе оригинального
     * и он помещается в "/public/uploads/"
     *
     * @param UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * Получение отфильтрованного, сортированного списка продуктов с доп. информацией для пагинации в виде json
     *
     * @param Request $request
     * @return string
     */
    public function getProducts(Request $request): string
    {
        $response = $this->entityManager->getRepository(Product::class)->getData($request);
        $max_entities = count($response);
        return $this->serializer->serialize(["data" => $response, "max_entities" => $max_entities, "per_page" => ProductRepository::PAGINATOR_PER_PAGE], 'json');
    }

    /**
     * Получение продукта по id в виде json 
     *
     * @param Request $request
     * @return string
     */
    public function getProduct(Request $request): string
    {
        $data = json_decode($request->getContent(), true);

        $product = $this->entityManager->getRepository(Product::class)->find($data["product_id"]);

        if (!$product) {
            throw new Exception("Page not found", 404);
        }
        return $this->serializer->serialize($product, 'json');
    }

    /**
     * Список справочников цветов, категорий и ролей пользователя для селектов 
     *
     * @return string
     */
    public function getAdditionalData(): string
    {
        $data = [];
        $data["product_color"] = $this->entityManager->getRepository(ProductColor::class)->findAll();
        $data["product_category"] = $this->entityManager->getRepository(ProductCategory::class)->getLowestLevel();
        $data["user_roles"] = $this->security->getUser()->getRoles();
        return $this->serializer->serialize($data, 'json');
    }

    /**
     * Удаление продукта по id в request
     *
     * @param Request $request
     * @return void
     */
    public function deleteProduct(Request $request)
    {
        $product = $this->entityManager->getRepository(Product::class)->find($request->get("product_id"));

        if (!$product) {
            throw new Exception("Page not found", 404);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    public function getDataBlob($product_id)
    {
        $product = $this->entityManager->getRepository(Product::class)->find($product_id);

        if (!$product) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }

        return $product;
    }

    /**
     * Запись из массива product_data в продукт
     *
     * @param Product $product
     * @param array $product_data
     * @return Product
     */
    public function fillProduct(Product $product, array $product_data): Product
    {
        $product->setShortDescription($product_data["short_description"] ? $product_data["short_description"] : null);
        $product->setDescription($product_data["description"] ? $product_data["short_description"] : null);
        $product->setAmount($product_data["amount"] ? $product_data["amount"] : null);
        $product->setWeight($product_data["weight"] ? $product_data["weight"] : null);
        $product->setAddedToStore($product_data["added_to_store"] ? date_create($product_data["added_to_store"]) : null);
        $product->setUpdated($product_data["updated"] ? \DateTime::createFromFormat("Y-m-d H:i:s", $product_data["updated"]) : null);
        $product->setProductCategory($product_data["product_category"] ? $this->entityManager->getReference(ProductCategory::class, $product_data["product_category"]) : null);
        $product->setProductColor($product_data["product_color"] ? $this->entityManager->getReference(ProductColor::class, $product_data["product_color"]) : null);
        return $product;
    }

    /**
     * Запись в базу данных файла блобом и его имени
     *
     * @param Product $product
     * @param [type] $blob
     * @return Product
     */
    public function setBlob(Product $product, $blob): Product
    {
        $content = file_get_contents($blob);
        $product->setBlob($content);
        $product->setBlobName($blob->getClientOriginalName());

        return $product;
    }
}
