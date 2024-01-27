<?php

declare(strict_types=1);

namespace Tbbc\MoneyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tbbc_money');
        $rootNode = $treeBuilder->getRootNode();
        $this->addCurrencySection($rootNode);

        return $treeBuilder;
    }

    /**
     * Parses the tbbc_money config section
     * Example for yaml driver:
     * tbbc_money:
     *     currencies: ["USD", "EUR"]
     *     reference_currency: "EUR".
     */
    private function addCurrencySection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('currencies')
                    ->scalarPrototype()->end()
                    ->defaultValue(['EUR', 'USD'])
                    ->requiresAtLeastOneElement()
                ->end()
                ->scalarNode('reference_currency')
                    ->defaultValue('EUR')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('enable_pair_history')
                    ->defaultValue(false)
                ->end()
                ->integerNode('decimals')
                    ->defaultValue(2)
                    ->min(0)
                ->end()
                ->scalarNode('storage')
                    ->cannotBeEmpty()
                    ->defaultValue('csv')
                    ->validate()
                    ->ifNotInArray(['csv', 'doctrine'])
                        ->thenInvalid('Invalid storage "%s"')
                    ->end()
                ->end()
                ->scalarNode('ratio_provider')
                    ->cannotBeEmpty()
                    ->defaultValue('tbbc_money.ratio_provider.ecb')
                ->end()
                ->arrayNode('templating')
                    ->addDefaultsIfNotSet()
                    ->setDeprecated('TbbcMoneyBundle', '5.2.0', 'Only Twig is supported')
                    ->children()
                        ->arrayNode('engines')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->example(['twig'])
                            ->beforeNormalization()
                                ->ifTrue(fn ($v) => !is_array($v))
                                ->then(fn ($v) => [$v])
                            ->end()
                            ->prototype('scalar')
                                ->validate()
                                    ->ifNotInArray(['twig', 'php'])
                                    ->thenInvalid('Only "twig" and "php" engines are supported.')
                                ->end()
                            ->end()
                            ->defaultValue(['twig'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
