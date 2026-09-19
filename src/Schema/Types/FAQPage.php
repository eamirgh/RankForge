<?php

namespace RankForge\Schema\Types;

class FAQPage extends AbstractType
{
    /** @var array<int, array<string, mixed>> */
    protected array $questionsList = [];

    public function __construct()
    {
        parent::__construct('FAQPage');
    }

    public function addQuestion(string $question, string $answer): static
    {
        $this->questionsList[] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];

        $this->setProperty('mainEntity', $this->questionsList);

        return $this;
    }

    /**
     * @param  array<int, array{question: string, answer: string}>|array<string, string>  $qaPairs
     */
    public function questions(array $qaPairs): static
    {
        foreach ($qaPairs as $question => $answer) {
            if (is_array($answer) && isset($answer['question'], $answer['answer'])) {
                $this->addQuestion($answer['question'], $answer['answer']);
            } elseif (is_string($question) && is_string($answer)) {
                $this->addQuestion($question, $answer);
            }
        }

        return $this;
    }
}
