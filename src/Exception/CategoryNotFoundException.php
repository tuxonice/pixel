<?php

declare(strict_types=1);

namespace App\Exception;

class CategoryNotFoundException extends \RuntimeException
{
    public function __construct(string $category)
    {
        parent::__construct(sprintf('Category "%s" not found.', $category));
    }
}
