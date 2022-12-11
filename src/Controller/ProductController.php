<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use Faker\Factory;
use Faker\Generator;

#[Route('/api', name: "api_")]
class ProductController extends AbstractController
{
    /** @var Generator */
    protected $faker;

    #[Route('/product/view', name: 'view_product', methods: ['GET', 'HEAD'])]
    public function viewAction(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $viewAllProduct = $em->getRepository(Product::class)
            ->findBy([
                "action" => ['I','U']
            ]);
        
        return $this->json($viewAllProduct);
    }

    #[Route('/product/view/{id}', name: 'view_product', methods: ['GET', 'HEAD'])]
    public function viewByIdAction(ManagerRegistry $doctrine, int $id): Response
    {
        $em = $doctrine->getManager();
        $viewByIdProduct = $em->getRepository(Product::class)
            ->findOneBy([
                "id" => $id
            ]);

        if(!$viewByIdProduct){
            $viewByIdProduct = ["messages" => "Product not found"];
        }

        return $this->json($viewByIdProduct);
    }

    #[Route('/product/faker', name: 'mock_prouct', methods: ['POST'])]
    public function fakeAction(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);
        $num = $data['qty'];
        $this->faker = Factory::create();

        for ($i = 0; $i < $num; $i++) {
            $mock = new Product();
            $mock->setName($this->faker->word());
            $mock->setQuantity($this->faker->numberBetween(0,500));
            $mock->setPrice($this->faker->numberBetween(20000,100000));
            $mock->setCategoryId(1);
            $mock->setAction("I");
            $mock->setAddTime($this->faker->dateTime());
            $em->persist($mock);
        }
        $em->flush();

        return $this->json(['message' => 'Success Create '.$i.' Mock Data']);
    }

    #[Route('/product/delete', name: 'delete_product', methods: ['POST'])]
    public function deleteAction(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        $product = $em->getRepository(Product::class)
            ->findOneBy([
                'id' => $data['id']
            ]);
        if(!$product){
            $product = ["messages" => "Product not found"];
        }
        $product->setAction('D');
        
        $em->persist($product);
        $em->flush();

        return $this->json(['message' => 'success delete data']);
    }
}
