<?php
namespace Sputnik\PackageManagerBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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


    public function index()
    {
        return $this->render('@PackageManager/templates/'.$this->templateManager->getCurrentBackend().'/index.html.twig');
    }

}