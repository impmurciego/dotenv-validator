<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Tests\Validator;

use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;
use Impmurciego\DotenvValidator\Exception\InvalidDotEnvException;
use Impmurciego\DotenvValidator\Output\SymfonyOutput;
use Impmurciego\DotenvValidator\Validator\EnvironmentsValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Impmurciego\DotenvValidator\Validator\EEnvironmentsValidator
 */
class EnvironmentsValidatorTest extends TestCase
{
    /**
     * @var MockObject&SymfonyOutput
     */
    private $outputMock;

    protected function setUp(): void
    {
        $this->outputMock = $this->createMock(SymfonyOutput::class);
    }

    public function testValidateEnvironmentsWithoutConfigFileShouldThrowsException(): void
    {
        $caughtException = null;
        try{
            $environmentsValidator = new EnvironmentsValidator($this->outputMock,__DIR__.'/../debug/dotenv-not-found.json');
            $environmentsValidator->validate();
        }catch (FileNotFoundException | InvalidConfigurationException | InvalidDotEnvException $exception) {
            $caughtException = $exception;
        }

        self::assertInstanceOf(FileNotFoundException::class, $caughtException);
    }

    public function testValidateEnvironmentsWithOnlyOneEnvironmentShouldThrowsException(): void
    {
        $caughtException = null;

        try{
            $environmentsValidator = new EnvironmentsValidator($this->outputMock,__DIR__.'/../debug/dotenv-one-environment.json');
            $environmentsValidator->validate();
        }catch (FileNotFoundException | InvalidConfigurationException | InvalidDotEnvException $exception) {
            $caughtException = $exception;
        }

        self::assertInstanceOf(InvalidConfigurationException::class, $caughtException);
    }
}
