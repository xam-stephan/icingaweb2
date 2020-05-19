<?php

namespace Icinga\Module\Dashboards\Form;

use Icinga\Authentication\Auth;
use Icinga\Module\Dashboards\Common\Database;
use Icinga\Module\Dashboards\Forms\DashboardsForm;
use Icinga\Web\Notification;

class DashletForm extends DashboardsForm
{
    use Database;

    /**
     * Display the FormElement for creating a new dashboards and dashlets
     */
    public function newAction()
    {
        $this->displayForm();

        $this->addElement('submit', 'submit', [
            'label' => 'Add To Dashboard'
        ]);
    }

    protected function assemble()
    {
        $this->add($this->newAction());
    }

    protected function onSuccess()
    {
        if ($this->getValue('new-dashboard-name') !== null) {
            $this->getDb()->insert('dashlet', [
                'dashboard_id'  => $this->createDashboard($this->getValue('new-dashboard-name')),
                'name'          => $this->getValue('name'),
                'url'           => $this->getValue('url'),
                'type'          => $this->getValue('dashboard-type'),
                'owner'         => $this->getValue('dashboard-type') === 'private'?
                    Auth::getInstance()->getUser()->getUsername(): null
            ]);

            Notification::success("Dashboard and dashlet created");
        } elseif ($this->checkForPrivateDashboard($this->getValue('dashboard')) &&
            $this->getValue('dashboard-type') === 'system') {
            Notification::error("Public dashlets in a private dashboard are not allowed!");
        } else {
            $this->getDb()->insert('dashlet', [
                'dashboard_id'  => $this->getValue('dashboard'),
                'name'          => $this->getValue('name'),
                'url'           => $this->getValue('url'),
                'type'          => $this->getValue('dashboard-type'),
                'owner'         => $this->getValue('dashboard-type') === 'private'?
                    Auth::getInstance()->getUser()->getUsername(): null
            ]);

            Notification::success("Dashlet created!");
        }
    }
}
