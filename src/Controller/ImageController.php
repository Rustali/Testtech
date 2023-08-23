<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class ImageController extends AbstractController
{
    #[Route('/')]
    public function index(): Response
    {
        return $this->render('image/index.html.twig');
    }

    #[Route('/process-url', name: 'process_url', methods: ['POST'])]
    public function processUrl(Request $request): Response
    {
        $url = $request->request->get('url');

        $client = new HttpBrowser(HttpClient::create());

        $crawler = $client->request('GET', $url);

        $images = $crawler->filter('img')->images();

        $imageInfo = [];
        $totalSizeMb = 0;

        foreach ($images as $image) {
            $imageUrl = $image->getUri();
            $imageSize = getimagesize($imageUrl);

            if ($imageSize !== false) {
                $imageWidth = $imageSize[0];
                $imageHeight = $imageSize[1];
                $imageSizeMb = $imageSize[0] * $imageSize[1] * $imageSize['bits'] / 8 / 1024 / 1024; // Размер в Мб
                $totalSizeMb += $imageSizeMb;

                $imageInfo[] = [
                    'url' => $imageUrl,
                    'width' => $imageWidth,
                    'height' => $imageHeight,
                    'size_mb' => $imageSizeMb,
                ];
            }
        }

        return $this->render('image/results.html.twig', [
            'url' => $url,
            'imageInfo' => $imageInfo,
            'totalSizeMb' => $totalSizeMb,
        ]);
    }
}