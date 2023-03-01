<?php

namespace App\Domain;

use App\Entity\Term;
use App\Entity\TextItem;

class RenderableCandidate {
    public ?Term $term = null;

    public string $text;
    public int $pos;
    public int $length;
    public int $isword;
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

    public function makeTextItem(int $seid, int $textid, int $langid): TextItem {
        $t = new TextItem();
        $t->Order = $this->pos;
        $t->TextID = $textid;
        $t->LangID = $langid;
        $t->Text = $this->text;
        $t->WordCount = $this->length;
        $t->TokenCount = $this->length;

        $t->TextLC = mb_strtolower($this->text);
        $t->SeID = $seid;
        $t->IsWord = $this->isword;
        $t->TextLength = mb_strlen($this->text);

        if ($this->term == null)
            return $t;

        $term = $this->term;
        $t->WoID = $term->getID();
        $t->WoText = $term->getText();
        $t->WoStatus = $term->getStatus();
        $t->WoTranslation = $term->getTranslation();
        $t->WoRomanization = $term->getRomanization();

        $i = $term->getCurrentImage();
        if ($i != null)
            $t->ImageSource = $i->getSource();

        $t->Tags = null;
        $tags = $term->getTermTags();
        if (count($tags) > 0) {
            $ts = [];
            foreach ($tags as $tag)
                $ts[] = $tag->getText();
            $t->Tags = implode(', ', $ts);
        }

        $p = $term->getParent();
        if ($p == null)
            return $t;

        $t->ParentWoID = $p->getID();
        $t->ParentWoTextLC = $p->getTextLC();
        $t->ParentWoTranslation = $p->getTranslation();

        $i = $p->getCurrentImage();
        if ($i != null)
            $t->ParentImageSource = $i->getSource();

        $t->ParentTags = null;
        $tags = $p->getTermTags();
        if (count($tags) > 0) {
            $ts = [];
            foreach ($tags as $tag)
                $ts[] = $tag->getText();
            $t->ParentTags = implode(', ', $ts);
        }

        return $t;
    }
}
