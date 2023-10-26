<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductColor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixture extends Fixture

{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("ru_RU");
        $images = ["test1.jpg", "test2.jpg", "test3.jpeg", "test4.jpeg"];
        $colors = $this->entityManager->getRepository(ProductColor::class)->findAll();
        $categories = $this->entityManager->getRepository(ProductCategory::class)->getLowestLevel();
        
        for ($i=0; $i < 50; $i++) { 
            $product = new Product();
            $description = $faker->realText($faker->numberBetween(1000, 2000));
            $product->setShortDescription($faker->realText(255));
            $product->setDescription($description);
            $product->setAmount($faker->numberBetween(0, 2000));
            $product->setWeight($faker->numberBetween(0, 2000) / 100);
            $product->setAddedToStore($faker->dateTime());
            $product->setUpdated($faker->dateTime());
            $product->setProductColor($colors[rand(0 , count($colors) - 1)]);
            $product->setProductCategory($categories[rand(0 , count($categories) - 1)]);
            $product->setImage($images[rand(0 , count($images) - 1)]);
            $product->setBlob("get");
            
            $manager->persist($product);
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return [
            ProductColor::class,
            ProductCategory::class,
        ];
    }
}
