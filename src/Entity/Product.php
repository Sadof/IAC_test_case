<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Ignore;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`product`')]

class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $short_description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $added_to_store = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?ProductCategory $product_category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?ProductColor $product_color = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    #[ignore]
    private $blob = null;

    #[ORM\Column(nullable: true)]
    private ?float $weight = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $blob_name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(?string $short_description): static
    {
        $this->short_description = $short_description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAddedToStore()
    {
        return $this->added_to_store ? $this->added_to_store->format("Y-m-d") : null ;
    }

    public function setAddedToStore(?\DateTimeInterface $added_to_store): static
    {
        $this->added_to_store = $added_to_store;

        return $this;
    }

    public function getUpdated()
    {
        return $this->updated ? $this->updated->format("Y-m-d H:d:s") : null ;
    }

    public function setUpdated(?\DateTimeInterface $updated): static
    {
        $this->updated = $updated;

        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->product_category;
    }

    public function setProductCategory(?ProductCategory $product_category): static
    {
        $this->product_category = $product_category;

        return $this;
    }

    public function getProductColor(): ?ProductColor
    {
        return $this->product_color;
    }

    public function setProductColor(?ProductColor $product_color): static
    {
        $this->product_color = $product_color;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image ? "/uploads/" . $this->image : "";
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getBlob()
    {
        return $this->blob;
    }

    public function setBlob($blob): static
    {
        $this->blob = $blob;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getBlobName(): ?string
    {
        return $this->blob_name;
    }

    public function setBlobName(?string $blob_name): static
    {
        $this->blob_name = $blob_name;

        return $this;
    }
}
