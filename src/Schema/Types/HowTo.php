<?php

namespace Eamirgh\RankForge\Schema\Types;

class HowTo extends AbstractType
{
    /** @var array<int, array<string, mixed>> */
    protected array $stepsList = [];

    public function __construct()
    {
        parent::__construct('HowTo');
    }

    public function name(string $name): static
    {
        return $this->setProperty('name', $name);
    }

    public function description(string $description): static
    {
        return $this->setProperty('description', $description);
    }

    public function addStep(string $name, string $text, ?string $url = null, ?string $image = null): static
    {
        $step = [
            '@type' => 'HowToStep',
            'name' => $name,
            'text' => $text,
        ];

        if ($url !== null) {
            $step['url'] = $url;
        }

        if ($image !== null) {
            $step['image'] = $image;
        }

        $this->stepsList[] = $step;

        $this->setProperty('step', $this->stepsList);

        return $this;
    }

    public function totalTime(string $iso8601Duration): static
    {
        return $this->setProperty('totalTime', $iso8601Duration);
    }

    /**
     * @param  array<string|array<string, mixed>>  $supplies
     */
    public function supply(array $supplies): static
    {
        $resolved = array_map(function (mixed $s) {
            if (is_string($s)) {
                return [
                    '@type' => 'HowToSupply',
                    'name' => $s,
                ];
            }

            return $s;
        }, $supplies);

        return $this->setProperty('supply', array_values($resolved));
    }

    /**
     * @param  array<string|array<string, mixed>>  $tools
     */
    public function tool(array $tools): static
    {
        $resolved = array_map(function (mixed $t) {
            if (is_string($t)) {
                return [
                    '@type' => 'HowToTool',
                    'name' => $t,
                ];
            }

            return $t;
        }, $tools);

        return $this->setProperty('tool', array_values($resolved));
    }
}
