<?php

declare(strict_types=1);

use App\Controller\ImageController;
use App\Service\ImageRepository;
use App\Service\RateLimiter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerBuilder $container): void {
    $container->register(ImageRepository::class, ImageRepository::class)
        ->addArgument($_ENV['IMAGES_ROOT'])
        ->addArgument($_ENV['APP_BASE_URL'])
        ->setPublic(true);

    $container->register(RateLimiter::class, RateLimiter::class)
        ->addArgument(dirname(__DIR__) . '/var/rate_limit')
        ->addArgument((int) ($_ENV['RATE_LIMIT_MAX'] ?? 60))
        ->addArgument((int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60))
        ->setPublic(true);

    $container->register(ImageController::class, ImageController::class)
        ->addArgument(new Reference(ImageRepository::class))
        ->setPublic(true);
};
