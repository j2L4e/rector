<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassNaming
{
    /**
     * @see https://regex101.com/r/8BdrI3/1
     * @var string
     */
    private const INPUT_HASH_NAMING_REGEX = '#input_(.*?)_#';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param string|Name|Identifier $name
     */
    public function getVariableName($name): string
    {
        $shortName = $this->getShortName($name);
        return lcfirst($shortName);
    }

    /**
     * @param string|Name|Identifier $name
     */
    public function getShortName($name): string
    {
        if ($name instanceof Name || $name instanceof Identifier) {
            $name = $this->nodeNameResolver->getName($name);
            if ($name === null) {
                throw new ShouldNotHappenException();
            }
        }

        $name = trim($name, '\\');

        return Strings::after($name, '\\', -1) ?: $name;
    }

    public function getNamespace(string $fullyQualifiedName): ?string
    {
        $fullyQualifiedName = trim($fullyQualifiedName, '\\');

        return Strings::before($fullyQualifiedName, '\\', -1) ?: null;
    }

    public function getNameFromFileInfo(SmartFileInfo $smartFileInfo): string
    {
        $basenameWithoutSuffix = $smartFileInfo->getBasenameWithoutSuffix();

        // remove PHPUnit fixture file prefix
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $basenameWithoutSuffix = Strings::replace($basenameWithoutSuffix, self::INPUT_HASH_NAMING_REGEX);
        }

        return StaticRectorStrings::underscoreToPascalCase($basenameWithoutSuffix);
    }

    /**
     * "some_function" → "someFunction"
     */
    public function createMethodNameFromFunction(Function_ $function): string
    {
        $functionName = (string) $function->name;
        return StaticRectorStrings::underscoreToCamelCase($functionName);
    }
}
