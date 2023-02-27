<?php

namespace App\Domain;

use App\Entity\Term;

class RenderableCandidate {
    public ?Term $term = null;

    public string $text;
    public int $pos;
    public int $length;
    public array $hides = array();
    public bool $render = true;

    public function getTermID(): ?int {
        if ($this->term == null)
            return null;
        return $this->term->getID();
    }
    
    public function OrderEnd(): int {
        return $this->pos + $this->length - 1;
    }

    public function toString(): string {
        $ren = $this->render ? 'true' : 'false';
        $id = $this->term != null ? $this->term->getID() : '-';
        return "{$id}; {$this->text}; {$this->pos}; {$this->length}; render = {$ren}";
    }
}
