<?php

declare(strict_types=1);

namespace Tests\Persistence;

require __DIR__ . '/../test-bootstrap.php';

use Config\Modules\Rendering\RenderingConfig;
use PublicContracts\Rendering\RenderingConfigInterface;
use Rendering\Infrastructure\RenderingKernel;
use Tests\IntegrationTestHelper;

final class RenderingTest extends IntegrationTestHelper
{
    private readonly string $testViewsDir;
    private readonly string $testCacheDir;
    private readonly RenderingConfigInterface $config;

    public function __construct()
    {
        parent::__construct('Rendering Module Test');
        $this->testViewsDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
        $this->testCacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'cache';

        $this->config = $this->configProvider->renderingConfig(
            $this->testViewsDir,
            $this->testCacheDir
        );
    }

    public function config(): RenderingConfigInterface
    {
        return $this->config;
    }
}

$testViewsDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
$testCacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
$testAssetsDir = __DIR__ . DIRECTORY_SEPARATOR . 'resources';

$config = new RenderingConfig($testViewsDir, $testCacheDir, $testAssetsDir);

$kernel = new RenderingKernel($config);
$renderer = $kernel->renderer();

$links = [
    ['label' => 'Home', 'url' => '/', 'active' => true],
    ['label' => 'Features', 'url' => '/features', 'active' => true],
    ['label' => 'Contact', 'url' => '/contact', 'active' => true],
    ['label' => 'About', 'url' => '/about', 'active' => true],
];

$viewData = [
    'pageTitle' => 'Test Page',
    'pageContent' => 'This is a test page content.',
    'featureList' => [
        ['title' => 'Feature 1', 'description' => 'Description for feature 1'],
        ['title' => 'Feature 2', 'description' => 'Description for feature 2'],
    ],
];

$bannerData1 = [
    'bannerTitle' => 'Welcome to the Test Page!',
    'bannerText' => 'This is a test banner to demonstrate partial rendering.',
    'buttonUrl' => '/learn-more',
    'buttonLabel' => 'Learn More',
];
$bannerData2 = [
    'bannerTitle' => 'Welcome to the Test Page!',
    'bannerText' => 'Use the template with multiple data sources.',
    'buttonUrl' => '/learn-more',
    'buttonLabel' => 'Learn More',
];
$bannerData3 = [
    'bannerTitle' => 'Welcome to the Test Page!',
    'bannerText' => 'This is a reausable banner template.',
    'buttonUrl' => '/learn-more',
    'buttonLabel' => 'Learn More',
];

$offerData = [
    'offerText' => 'Use the code for 25% discount:',
    'discountCode' => 'SALE2025'
];

$bannerPartials = ['special-offer' => ['partial/_special-offer.phtml', $offerData]];

$assets = [
    'style.css',
    'app.js',
];

$renderer
    ->setTitle('Test Page')
    ->setView('view.phtml', $viewData)
    ->setNavigationLinks($links)
    ->addPartial('welcome-banner-1', 'partial/welcome-banner.phtml', $bannerData1, $bannerPartials)
    ->addPartial('welcome-banner-2', 'partial/welcome-banner.phtml', $bannerData2, $bannerPartials)
    ->addPartial('welcome-banner-3', 'partial/welcome-banner.phtml', $bannerData3, $bannerPartials)
    ->setAssets($assets)
;

$output = $renderer->render();
echo $output;
