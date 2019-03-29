<?php
namespace Sputnik\PackageManagerBundle\Controller\Backend;

use Sputnik\CoreBundle\Service\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class GeneralController extends AbstractController
{
    
    /**
     * @var $templateManager TemplateManager
     */
    private $templateManager;
    
    /**
     * @var $cacheManager CacheManager
     */
    private $cacheManager;

    public function __construct(TemplateManager $templateManager, CacheManager $cacheManager)
    {
        $this->templateManager = $templateManager;
        $this->cacheManager = $cacheManager;
    }

    
    public function index()
    {
        return $this->render($this->templateManager->getCurrentBackend().'/index.html.twig');
    }
    
    /**
     * Installing composer
     * @Route("/admin/composer/install", name="admin_composer_install")
     * @return Response
     * @throws \Exception
     */
    public function composerInstall()
    {
        // check permissions
        $this->denyAccessUnlessGranted('install', 'composer');

        require_once __DIR__.'/../../../../../../../vendor/autoload.php';

        // Composer\Factory::getHomeDir() method
        // needs COMPOSER_HOME environment variable set
        putenv('COMPOSER_HOME=' . __DIR__.'/../../../../../../../vendor/bin/composer');

        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => 'install'));
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $c = $application->run($input);

        return new Response($c);
    }

    /**
     * command: install, update, remove
     * vendor: shandyrov
     * package: blogbundle
     * @Route("/admin/composer/{command}/{vendor}/{package}/{clearcache}", name="admin_composer_run_command", requirements={"command"="\w+", "vendor"="\w+", "package"="\w+", "clearcache"="\d+"})
     * @param  $command
     * @param  $vendor
     * @param  $package
     * @param  $clear_cache
     * @return Response
     */
    public function runCommand($command, $vendor = null, $package = null, $clearcache = 0)
    {
        // check permissions
        $this->denyAccessUnlessGranted('command', 'composer');

        $composerPath = __DIR__.'/../../../../../../../vendor/bin/composer';
        chdir(__DIR__.'/../../../../../../../');

        // check composer is installed
        $isInstalled = $this->runCommandWithArgument('diagnose');

        //var_dump($isInstalled);die;
        if ($isInstalled) {
            
            if ($vendor && $package) {
                $process = new Process([$composerPath, $command, $vendor.'/'.$package]);
            } else {
                // only command: update, install
                $process = new Process([$composerPath, $command]);
            }

            $process->setTimeout(360);

            try {

                $process->mustRun();

            } catch (ProcessFailedException $exception) {
                return new Response($exception->getMessage());
            }


            if ($clearcache) {
                $this->cacheManager->clearCache();
            }

            return new Response('process:'.$process->getExitCodeText());
        }
        return new Response('Composer Not Installed!');
    }

    /**
     * @param $command
     * @param null $argument
     * @return int
     */
    private function runCommandWithArgument($command, $argument = null)
    {
        $composerPath = __DIR__.'/../../../../../../../vendor/bin/composer';
        chdir(__DIR__.'/../../../../../../../');

        if (!$argument) {
            $process = new Process([$composerPath, $command]);
        } else {
            $process = new Process([$composerPath, $command, $argument]);
        }
        $process->setTimeout(60);

        try {

            $process->mustRun();

        } catch (ProcessFailedException $exception) {
            return 0;
        }
        return 1;
    }
}
