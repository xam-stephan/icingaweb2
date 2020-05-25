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
            $dashboardId = $this->createDashboard($this->getValue('new-dashboard-name'));
            $dashletId = $this->insertIntoDashlet($dashboardId);

            if ($this->getValue('dashboard-type') === 'system') {
                $this->getDb()->insert('dashboard_order', [
                    'dashboard_id'  => $dashboardId,
                    '`order`'       => $dashboardId
                ]);

                $this->getDb()->insert('dashlet_order', [
                    'dashlet_id' => $dashletId,
                    '`order`'   => $dashletId
                ]);
            } else {
                $this->getDb()->insert('dashboard_user_order', [
                    'dashboard_id'  => $dashboardId,
                    'username'  => Auth::getInstance()->getUser()->getUsername(),
                    '`order`'       => $dashboardId
                ]);

                $this->getDb()->insert('dashlet_user_order', [
                    'dashlet_id' => $dashletId,
                    'username'  => Auth::getInstance()->getUser()->getUsername(),
                    '`order`'   => $dashletId
                ]);
            }

            Notification::success("Dashboard and dashlet created");
        } elseif ($this->checkForPrivateDashboard($this->getValue('dashboard')) &&
            $this->getValue('dashboard-type') === 'system') {
            Notification::error("Public dashlets in a private dashboard are not allowed!");
        } else {
            $dashletId = $this->insertIntoDashlet($this->getValue('dashboard'));

            if ($this->getValue('dashboard-type') === 'system') {
                $this->getDb()->insert('dashlet_order', [
                    'dashlet_id' => $dashletId,
                    '`order`'   => $dashletId
                ]);
            } else {
                $this->getDb()->insert('dashlet_user_order', [
                    'dashlet_id' => $dashletId,
                    'username'  => Auth::getInstance()->getUser()->getUsername(),
                    '`order`'   => $dashletId
                ]);
            }

            Notification::success("Dashlet created!");
        }
    }
}
