<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

use \Zend_Controller_Action_Exception;
use Icinga\Application\Config;
use Icinga\Authentication\UserGroup\UserGroupBackend;
use Icinga\Authentication\UserGroup\UserGroupBackendInterface;
use Icinga\Web\Controller;
use Icinga\Web\Form;
use Icinga\Web\Widget;

class GroupController extends Controller
{
    /**
     * Initialize this controller
     */
    public function init()
    {
        parent::init();
        $this->createTabs();
    }

    /**
     * Redirect to this controller's list action
     */
    public function indexAction()
    {
        $this->redirectNow('group/list');
    }

    /**
     * List all user groups of a single backend
     */
    public function listAction()
    {
        $backendNames = array_map(
            function ($b) { return $b->getName(); },
            $this->loadUserGroupBackends('Icinga\Data\Selectable')
        );
        $this->view->backendSelection = new Form();
        $this->view->backendSelection->setAttrib('class', 'backend-selection');
        $this->view->backendSelection->setUidDisabled();
        $this->view->backendSelection->setMethod('GET');
        $this->view->backendSelection->setTokenDisabled();
        $this->view->backendSelection->addElement(
            'select',
            'backend',
            array(
                'autosubmit'    => true,
                'label'         => $this->translate('Usergroup Backend'),
                'multiOptions'  => array_combine($backendNames, $backendNames),
                'value'         => $this->params->get('backend')
            )
        );

        $backend = $this->getUserGroupBackend($this->params->get('backend'));
        if ($backend === null) {
            $this->view->backend = null;
            return;
        }

        $query = $backend->select(array(
            'group_name',
            'parent_name',
            'created_at',
            'last_modified'
        ));

        $filterEditor = Widget::create('filterEditor')
            ->setQuery($query)
            ->preserveParams('limit', 'sort', 'dir', 'view', 'backend')
            ->ignoreParams('page')
            ->handleRequest($this->getRequest());
        $query->applyFilter($filterEditor->getFilter());
        $this->setupFilterControl($filterEditor);

        $this->getTabs()->activate('group/list');
        $this->view->backend = $backend;
        $this->view->groups = $query->paginate();

        $this->setupLimitControl();
        $this->setupPaginationControl($this->view->groups);
        $this->setupSortControl(
            array(
                'group_name'    => $this->translate('Group'),
                'parent_name'   => $this->translate('Parent'),
                'created_at'    => $this->translate('Created at'),
                'last_modified' => $this->translate('Last modified')
            ),
            $query
        );
    }

    /**
     * Return all user group backends implementing the given interface
     *
     * @param   string  $interface      The class path of the interface, or null if no interface check should be made
     *
     * @return  array
     */
    protected function loadUserGroupBackends($interface = null)
    {
        $backends = array();
        foreach (Config::app('groups') as $backendName => $backendConfig) {
            $candidate = UserGroupBackend::create($backendName, $backendConfig);
            if (! $interface || $candidate instanceof $interface) {
                $backends[] = $candidate;
            }
        }

        return $backends;
    }

    /**
     * Return the given user group backend or the first match in order
     *
     * @param   string  $name           The name of the backend, or null in case the first match should be returned
     * @param   string  $interface      The interface the backend should implement, no interface check if null
     *
     * @return  UserGroupBackendInterface
     *
     * @throws  Zend_Controller_Action_Exception    In case the given backend name is invalid
     */
    protected function getUserGroupBackend($name = null, $interface = 'Icinga\Data\Selectable')
    {
        if ($name !== null) {
            $config = Config::app('groups');
            if (! $config->hasSection($name)) {
                throw new Zend_Controller_Action_Exception(
                    sprintf($this->translate('User group backend "%s" not found'), $name),
                    404
                );
            } else {
                $backend = UserGroupBackend::create($name, $config->getSection($name));
                if ($interface && !$backend instanceof $interface) {
                    $interfaceParts = explode('\\', strtolower($interface));
                    throw new Zend_Controller_Action_Exception(
                        sprintf(
                            $this->translate('User group backend "%s" is not %s'),
                            $name,
                            array_pop($interfaceParts)
                        ),
                        400
                    );
                }
            }
        } else {
            $backends = $this->loadUserGroupBackends($interface);
            $backend = array_shift($backends);
        }

        return $backend;
    }

    /**
     * Create the tabs
     */
    protected function createTabs()
    {
        $tabs = $this->getTabs();
        $tabs->add(
            'user/list',
            array(
                'title'     => $this->translate('List users of authentication backends'),
                'label'     => $this->translate('Users'),
                'icon'      => 'users',
                'url'       => 'user/list'
            )
        );
        $tabs->add(
            'group/list',
            array(
                'title'     => $this->translate('List groups of user group backends'),
                'label'     => $this->translate('Groups'),
                'icon'      => 'cubes',
                'url'       => 'group/list'
            )
        );
    }
}
