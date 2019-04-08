<?php
namespace Sputnik\PackageManagerBundle\Controller\Backend;

use Sputnik\TemplateBundle\Service\TemplateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralController extends AbstractController
{
    /**
     * @var $templateManager TemplateManager
     */
    private $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }

    public function index(Request $request)
    {

        return $this->render('@PackageManager/templates/'.$this->templateManager->getCurrentBackend().'/general/index.html.twig');
    }

}
