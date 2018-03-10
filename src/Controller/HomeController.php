<?php

namespace App\Controller;

use App\Contributors\Service\Repository as ContributorsRepository;
use App\Contributors\Model\Contributor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends Controller
{
    /**
     * @param ContributorsRepository $contributorsRepository
     * @return Response
     */
    public function index(ContributorsRepository $contributorsRepository): Response
    {
        /** @var Contributor[] $contributors */
        $contributors = $contributorsRepository->all();

        return $this->render(
            'home/index.html.twig', [
                'contributors' => $contributors
            ]
        );
    }
}
