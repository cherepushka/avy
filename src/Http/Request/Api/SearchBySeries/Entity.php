<?php

namespace App\Http\Request\Api\SearchBySeries;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Entity
{

    #[Assert\NotBlank(message: "Текст запроса не должен быть пустым")]
    private string $search;

    /**
     * @var int[]|null
     */
    #[Assert\Callback([Entity::class, 'validateSeries'])]
    private ?array $series = null;

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getSeries(): ?array
    {
        return $this->series;
    }

    /**
     * @param int[]|null $series
     */
    public function setSeries(?array $series): self
    {
        $this->series = $series;

        return $this;
    }

    public static function validateSeries(mixed $object, ExecutionContextInterface $context, $payload): void
    {
        if ($object === null) {
            return;
        }

        if (!is_array($object)){
            $context->buildViolation('Серии должны быть массивом')
                ->atPath('series')
                ->addViolation();
            return;
        }

        foreach ($object as $value){
            if (!is_int($value)) {
                $context->buildViolation('Серии должны быть числами')
                    ->atPath('series')
                    ->addViolation();
                return;
            }
        }

    }

}