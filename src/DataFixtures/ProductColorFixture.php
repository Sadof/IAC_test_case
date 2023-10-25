<?php

namespace App\DataFixtures;

use App\Entity\ProductColor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductColorFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $colors = [
            "Белый",
            "Черный",
            "Красный",
            "Синий",
            "Желтый",
            "Розовый",
            "Серый",
            "Голубой",
            "Зеленый",
            "Оранжевый",
            "Фиолетовый",
        ];

        foreach($colors as $color_name){
            $color = new ProductColor();
            $color->setName($color_name);
            $manager->persist($color);
        }
        $manager->flush();
    }
}
