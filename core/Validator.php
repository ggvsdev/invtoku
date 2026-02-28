<?php
declare(strict_types=1);

class Validator {
    private array $errors = [];
    private array $data = [];
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function required(string $field, string $message = null): self {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?? "El campo $field es requerido";
        }
        return $this;
    }
    
    public function min(string $field, int $length, string $message = null): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? "Mínimo $length caracteres requeridos";
        }
        return $this;
    }
    
    public function max(string $field, int $length, string $message = null): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? "Máximo $length caracteres permitidos";
        }
        return $this;
    }
    
    public function email(string $field, string $message = null): self {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Email inválido";
        }
        return $this;
    }
    
    public function numeric(string $field, string $message = null): self {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "Debe ser numérico";
        }
        return $this;
    }
    
    public function positive(string $field, string $message = null): self {
        if (isset($this->data[$field]) && $this->data[$field] <= 0) {
            $this->errors[$field] = $message ?? "Debe ser mayor a cero";
        }
        return $this;
    }
    
    public function fails(): bool {
        return !empty($this->errors);
    }
    
    public function errors(): array {
        return $this->errors;
    }
    
    public function first(): ?string {
        return $this->errors[array_key_first($this->errors)] ?? null;
    }
}