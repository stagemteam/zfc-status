<?php
namespace Agere\Status\Service;

use Agere\Core\Service\DomainServiceAbstract;
use Agere\Module\Controller\Plugin\ModulePlugin;
use Agere\Status\Model\Repository\ProgressRepository;
use Agere\Status\Model\Progress;

class ProgressService extends DomainServiceAbstract {

    protected $entity = Progress::class;

    protected $user;

    /** @var ModulePlugin */
    protected $moduleHelper;

    public function __construct($user, ModulePlugin $modulePlugin) {
        $this->user = $user;
        $this->moduleHelper = $modulePlugin;
    }

    public function setUser($user) {
        $this->user = $user;

        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function getModuleHelper() {
        return $this->moduleHelper;
    }

    public function getProgress($item) {
        $module = $this->moduleHelper->setRealContext($item)->getModule();
        /** @var ProgressRepository $repository */
        $repository = $this->getRepository();

        return $repository->getProgressItem($item, $module);
    }

    public function writeProgress($item, $status) {
        $module = $this->moduleHelper->setRealContext($item)->getModule();
        /** @var Progress $progress */
        $progress = $this->getObjectModel();
        if (!$item->getId()) { // @todo Щоб уникнути не бажаного flush реалізувати single_table або розібратись у Statusable (від Taggable, Sortable etc.)
            $this->getObjectManager()->flush();
        }
        //\Zend\Debug\Debug::dump($item->getId()); die(__METHOD__); // ця помилка у create методі так як у нас немає id, а чомусь у edit наших статусів не видно. Не розумію чому

        $progress->setItemId($item->getId());
        //$progress->setItemId(1);
        $progress->setUser($this->user)
            ->setStatus($status)
            ->setModule($module)
            ->setModifiedAt(new \DateTime('now'))
            ->setSnippet(serialize($item))
        ;

        /*\Zend\Debug\Debug::dump([
            $progress->getId() . '$progress->getId()',
            $this->user->getId() . '$this->user->getId()',
            $item->getId() . '$item->getId()',
            $status->getId() . '$status->getId()',
            $module->getId() . '$module->getId()',

        ]); die(__METHOD__);*/

        $this->getObjectManager()->persist($progress);
        
        return $this;
    }

}