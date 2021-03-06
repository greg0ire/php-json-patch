<?php

namespace Remorhaz\JSON\Patch;

use Remorhaz\JSON\Data\Exception as DataException;
use Remorhaz\JSON\Data\ReaderInterface;
use Remorhaz\JSON\Data\SelectorInterface;
use Remorhaz\JSON\Pointer\Pointer;

class Patch
{

    private $inputSelector;

    private $outputSelector;

    private $patchSelector;

    private $dataPointer;

    private $patchPointer;

    public function __construct(SelectorInterface $dataSelector)
    {
        $this->setInputSelector($dataSelector);
    }

    public function apply(SelectorInterface $patchSelector)
    {
        $this
            ->setPatchSelector($patchSelector)
            ->getPatchSelector()
            ->selectRoot();
        if (!$this->getPatchSelector()->isArray()) {
            throw new \RuntimeException("Patch must be an array");
        }
        $this->setOutputSelector($this->getInputSelector());
        $operationCount = $this
            ->getPatchSelector()
            ->getElementCount();
        for ($operationIndex = 0; $operationIndex < $operationCount; $operationIndex++) {
            $this->performOperation($operationIndex);
        }
        $this->setInputSelector($this->getOutputSelector());
        return $this;
    }

    public function getResult(): ReaderInterface
    {
        return $this->getOutputSelector();
    }

    protected function setInputSelector(SelectorInterface $selector)
    {
        $this->inputSelector = $selector;
        return $this;
    }

    protected function getInputSelector(): SelectorInterface
    {
        if (null === $this->inputSelector) {
            throw new \LogicException("Input selector is empty");
        }
        return $this->inputSelector;
    }

    protected function setOutputSelector(SelectorInterface $selector)
    {
        $this->outputSelector = $selector;
        return $this;
    }

    protected function getOutputSelector(): SelectorInterface
    {
        if (null === $this->outputSelector) {
            throw new \LogicException("Output selector is empty");
        }
        return $this->outputSelector;
    }

    protected function performOperation(int $index)
    {
        $operation = $this->getPatchPointer()->read("/{$index}/op")->getAsString();
        $path = $this->getPatchPointer()->read("/{$index}/path")->getAsString();
        switch ($operation) {
            case 'add':
                $valueReader = $this->getPatchPointer()->read("/{$index}/value");
                $this->getDataPointer()->add($path, $valueReader);
                break;

            case 'remove':
                $this->getDataPointer()->remove($path);
                break;

            case 'replace':
                $valueReader = $this->getPatchPointer()->read("/{$index}/value");
                $this->getDataPointer()->replace($path, $valueReader);
                break;

            case 'test':
                $expectedValueReader = $this->getPatchPointer()->read("/{$index}/value");
                $actualValueReader = $this->getDataPointer()->read($path);
                try {
                    // TODO: Make reader's test() method boolean and refactor pointer's test().
                    $expectedValueReader->test($actualValueReader);
                } catch (DataException $e) {
                    throw new \RuntimeException("Test operation failed", 0, $e);
                }
                break;

            case 'copy':
                $from = $this->getPatchPointer()->read("/{$index}/from")->getAsString();
                $valueReader = $this->getDataPointer()->read($from);
                $this->getDataPointer()->add($path, $valueReader);
                break;

            case 'move':
                $from = $this->getPatchPointer()->read("/{$index}/from")->getAsString();
                $valueReader = $this->getDataPointer()->read($from);
                $this
                    ->getDataPointer()
                    ->remove($from)
                    ->add($path, $valueReader);
                break;

            default:
                throw new \RuntimeException("Unknown operation '{$operation}'");
        }
        return $this;
    }


    protected function setPatchSelector(SelectorInterface $patchReader)
    {
        $this->patchSelector = $patchReader;
        return $this;
    }


    protected function getPatchSelector(): SelectorInterface
    {
        if (null === $this->patchSelector) {
            throw new \LogicException("Patch reader is not set");
        }
        return $this->patchSelector;
    }


    protected function getPatchPointer(): Pointer
    {
        if (null === $this->patchPointer) {
            $this->patchPointer = new Pointer($this->getPatchSelector());
        }
        return $this->patchPointer;
    }


    protected function getDataPointer(): Pointer
    {
        if (null === $this->dataPointer) {
            $this->dataPointer = new Pointer($this->getInputSelector());
        }
        return $this->dataPointer;
    }
}
