<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductCategotyFixture extends Fixture
{
    protected ObjectManager $manager;
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $categories = [
            [
                "name" => "Бытовая техника",
                "childrens" => [
                    [
                        "name" => "Мониторы",
                        "childrens" => [
                            [
                                "name" => "Офисные",
                                "childrens" => []
                            ],
                            [
                                "name" => "Игровые",
                                "childrens" => []
                            ],

                        ]
                    ],
                    [
                        "name" => "Телевизоры",
                        "childrens" => [
                            [
                                "name" => "Smart-модели",
                                "childrens" => []
                            ],
                            [
                                "name" => "3D-модели",
                                "childrens" => []
                            ],
                            [
                                "name" => "Универсальные модели",
                                "childrens" => []
                            ],

                        ]
                    ],
                    [
                        "name" => "Кухонные электроприборы",
                        "childrens" => [
                            [
                                "name" => "Чайники",
                                "childrens" => []
                            ],
                            [
                                "name" => "Посудомойки",
                                "childrens" => []
                            ],
                            [
                                "name" => "Микроволновки",
                                "childrens" => []
                            ],
                            [
                                "name" => "Мультиварки",
                                "childrens" => []
                            ],
                            [
                                "name" => "Миксеры",
                                "childrens" => []
                            ],
                            [
                                "name" => "Блендеры",
                                "childrens" => []
                            ],

                        ]
                    ],
                ]
            ]
        ];

        
        $this->addChildrens($categories);

        $this->manager->flush();
    }

    private function addChildrens($categories, $parent_id=null){
        foreach ($categories as $category){
            $category_obj = new ProductCategory();
            $category_obj->setName($category["name"]);
            $category_obj->setParentId($parent_id);
            $this->manager->persist($category_obj);
            if (!empty($category["childrens"])){
                $this->addChildrens($category["childrens"], $category_obj->getId());
            }
        }
    }
}
