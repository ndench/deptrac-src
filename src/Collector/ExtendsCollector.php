<?php

declare(strict_types=1);

namespace Qossmic\Deptrac\Collector;

use LogicException;
use Qossmic\Deptrac\AstRunner\AstMap;
use Qossmic\Deptrac\AstRunner\AstMap\AstClassReference;
use Qossmic\Deptrac\AstRunner\AstMap\ClassLikeName;

class ExtendsCollector implements CollectorInterface
{
    public function resolvable(array $configuration, Registry $collectorRegistry, array $resolutionTable): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function satisfy(
        array $configuration,
        AstMap\AstTokenReference $astTokenReference,
        AstMap $astMap,
        Registry $collectorRegistry,
        array $resolutionTable = []
    ): bool {
        if (!$astTokenReference instanceof AstClassReference) {
            return false;
        }

        $interfaceName = $this->getInterfaceName($configuration);

        foreach ($astMap->getClassInherits($astTokenReference->getTokenName()) as $inherit) {
            if ($inherit->isExtends() && $inherit->getClassLikeName()->equals($interfaceName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function getInterfaceName(array $configuration): ClassLikeName
    {
        if (isset($configuration['extends']) && !isset($configuration['value'])) {
            trigger_deprecation('qossmic/deptrac', '0.20.0', 'ExtendsCollector should use the "value" key from this version');
            $configuration['value'] = $configuration['extends'];
        }

        if (!isset($configuration['value']) || !is_string($configuration['value'])) {
            throw new LogicException('ExtendsCollector needs the interface or class name as a string.');
        }

        return ClassLikeName::fromFQCN($configuration['value']);
    }
}
