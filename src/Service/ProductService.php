<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class ProductService
{
    private $entityManager;
    private $validator;
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }
    public function fillProduct($product_data, $files, $product = null)
    {
        if (!$product) {
            $product = new Product();
        }

        $file_name = null;

        if ($files->get("image")) {
            $image_check = new Assert\File(["extensions" =>  ['jpg', 'jpeg', 'png'], "maxSize" => "2M"]);
            $errors = $this->validator->validate($files->get("image"), $image_check);

            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new Exception($errorsString);
            }

            $file_name = $this->upload($files->get("image"));
        }

        $product->setShortDescription($product_data["short_description"] ? $product_data["short_description"] : null);
        $product->setDescription($product_data["description"] ? $product_data["short_description"] : null);
        $product->setAmount($product_data["amount"] ? $product_data["amount"] : null);
        $product->setWeight($product_data["weight"] ? $product_data["weight"] : null);
        $product->setAddedToStore($product_data["added_to_store"] ? date_create($product_data["added_to_store"]) : null);
        $product->setUpdated($product_data["updated"] ? \DateTime::createFromFormat("Y-m-d H:i:s", $product_data["updated"]) : null);
        $product->setProductCategory($product_data["product_category"] ? $this->entityManager->getReference(ProductCategory::class, $product_data["product_category"]) : null);
        $product->setProductColor($product_data["product_color"] ? $this->entityManager->getReference(ProductColor::class, $product_data["product_color"]) : null);
        if ($file_name) {
            $product->setImage($file_name);
        }
        $product->setBlob($product_data["blob"]);

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new Exception($errorsString);
        }

        return $product;
    }

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
}
