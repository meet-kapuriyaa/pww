<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $material;
    protected $required;
    protected $available;

    public function __construct(string $material, float $required, float $available)
    {
        $this->material = $material;
        $this->required = $required;
        $this->available = $available;
        
        parent::__construct("Insufficient stock for raw material: {$material}. Required: {$required}, Available: {$available}");
    }

    public function getMaterial()
    {
        return $this->material;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function getAvailable()
    {
        return $this->available;
    }
}
