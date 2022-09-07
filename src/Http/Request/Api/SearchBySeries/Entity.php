<?php

namespace App\Http\Request\Api\SearchBySeries;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Entity
{

    #[Assert\NotBlank(message: "Текст запроса не должен быть пустым")]
    #[Assert\Length(min: 1, minMessage: "Текст запроса должен соддержать больше одного символа")]
    private string $search;

    /**
     * @var int[]|null
     */
    #[Assert\Callback([Entity::class, 'validateSeries'])]
    private ?array $series = null;

    #[Assert\Callback([Entity::class, 'validatePage'])]
    private int $page = 1;

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
        if (empty($series)) {
            return $this;
        }

        $this->series = $series;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(?int $page): self
    {
        if (null === $page) {
            return $this;
        }

        $this->page = $page;

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

    public static function validatePage(mixed $object, ExecutionContextInterface $context, $payload): void
    {
        if ($object === null) {
            return;
        }

        if (!is_int($object)){
            $context->buildViolation('Страница должна быть числом')
                ->atPath('page')
                ->addViolation();
        }
    }

}