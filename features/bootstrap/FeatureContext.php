<?php

class FeatureContext extends \Behatch\Context\RestContext
{
    /**
     * @var \App\DataFixtures\AppFixtures
     */
    private $fixtures;

    /**
     * @var \Coduo\PHPMatcher\Matcher
     */
    private $matcher;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(
        \Behatch\HttpCall\Request $request,
        \App\DataFixtures\AppFixtures $fixtures,
        \Doctrine\ORM\EntityManagerInterface $em
    ) {
        parent::__construct($request);
        $this->fixtures = $fixtures;
        $this->matcher =
            (new \Coduo\PHPMatcher\Factory\SimpleFactory())->createMatcher();
        $this->em = $em;
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createSchema()
    {
        // Get entity metadata
        $classes = $this->em->getMetadataFactory()
            ->getAllMetadata();

        // Drop and create schema
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        // Load fixtures... and execute
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->em);
        $fixturesExecutor =
            new \Doctrine\Common\DataFixtures\Executor\ORMExecutor(
                $this->em,
                $purger
            );

        $fixturesExecutor->execute([
            $this->fixtures
        ]);
    }
}
