<?php

namespace App\Service;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SnippetGenerator
 * @package App\Service
 */
class SnippetGenerator implements SnippetGeneratorInterface
{
    /**
     * @var RouterInterface $router
     */
    private $router;

    /**
     * @var array $badges
     */
    private $badges;

    /**
     * @var array $allInBadges
     */
    private $allInBadges;

    /**
     * @var null|RouteCollection
     */
    private $routes;

    /**
     * @var string $packagistRoute
     */
    private $packagistRoute;

    public function __construct(RouterInterface $router, array $allInBadges, array $badges, string $packagist_route = 'pugx_badge_packagist')
    {
        $this->router = $router;
        $this->routes = $router->getRouteCollection();
        $this->allInBadges = $allInBadges;
        $this->badges = $badges;
        $this->packagistRoute = $packagist_route;
    }

    /**
     * @param string $repository
     * @return array
     */
    public function generateAllSnippets(string $repository): array
    {
        $snippets = [];
        $snippets['clip_all']['markdown'] = '';

        foreach ($this->badges as $badge) {
            $markdown = $this->generateMarkdown($badge, $repository);
            $snippets[$badge['name']] = [
                'markdown'  => $markdown,
                'img'       => $this->generateImg($badge, $repository)
            ];

            if (in_array($badge['name'], $this->allInBadges)) {
                $snippets['clip_all']['markdown'] .= ' '.$markdown;
            }
        }

        $snippets['clip_all']['markdown'] = trim($snippets['clip_all']['markdown']);
        $snippets['repository']['html'] = $repository;

        return $snippets;
    }

    /**
     * @param array $badge
     * @param string $repository
     * @return string
     * @throws \Exception
     */
    public function generateMarkdown(array $badge, string $repository): string
    {
        return sprintf(
            '[![%s](%s)](%s)',
            $badge['label'],
            $this->generateImg($badge, $repository),
            $this->generateRepositoryLink($repository)
        );
    }

    /**
     * @param array $badge
     * @param string $repository
     * @return string
     * @throws \Exception
     */
    public function generateImg(array $badge, string $repository): string
    {
        $badge['repository'] = $repository;
        $parameters = $this->compileRouteParametersForBadge($badge);

        return $this->router->generate($badge['route'], $parameters, true);
    }

    /**
     * @param string $repository
     * @return string
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function generateRepositoryLink(string $repository): string
    {
        return $this->router->generate($this->packagistRoute, ['repository' => $repository], true);
    }

    /**
     * @param array $badge
     * @return array
     * @throws \RuntimeException
     */
    private function compileRouteParametersForBadge(array $badge): array
    {
        $parameters = [];
        $route = $this->routes->get($badge['route']);

        if (!$route) {
            throw new \RuntimeException(sprintf('The route "%s" was not found', $badge['route']));
        }

        $routeParameters = array_keys(array_merge($route->getDefaults(), $route->getRequirements()));

        foreach ($routeParameters as $routeParameter) {
            if (array_key_exists($routeParameter, $badge)) {
                $parameters[$routeParameter] = $badge[$routeParameter];
            }
        }

        return $parameters;
    }
}
