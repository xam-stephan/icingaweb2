<?php

namespace Icinga\Module\Dashboards\Form;

use Icinga\Module\Dashboards\Common\Database;
use Icinga\Module\Dashboards\Forms\DashboardsForm;
use Icinga\Web\Notification;

class EditDashletForm extends DashboardsForm
{
    use Database;

    /** @var object $dashboard from which the dashlet comes from */
    protected $dashboard;

    /** @var object $dashlet of the selected dashboard */
    protected $dashlet;

    /**
     * get a dashlet from the selected dashboard
     *
     * and populate it's details to the dashlet form
     *
     * @param $dashlet
     *
     * @param $dashboard
     */
    public function __construct($dashlet, $dashboard)
    {
        $this->dashlet = $dashlet;
        $this->dashboard = $dashboard;

        if (! empty($dashlet)) {
            $this->populate([
                'url' => $dashlet->url,
                'name' => $dashlet->name,
                'dashboard'      => $dashboard->id
            ]);
        }
    }

    /**
     * Display the FormElement for editing dashboards and dashlets
     */
    public function editAction()
    {
        $this->displayForm();

        $this->addElement('submit', 'submit', [
            'label' => 'Update Dashboard'
        ]);
    }

    protected function assemble()
    {
        $this->add($this->editAction());
    }

    protected function onSuccess()
    {
        if (! empty($this->getValue('new-dashboard-name'))) {
            $this->updateDashletTable(
                $this->dashlet,
                $this->createDashboard($this->getValue('new-dashboard-name'))
            );

            Notification::success('Dashboard created & dashlet updated');
        } elseif ($this->checkForPrivateDashboard($this->getValue('dashboard')) &&
            $this->getValue('dashboard-type') === 'system') {
            Notification::error("Public dashlets in a private dashboard are not allowed!");
        } elseif ($this->dashboard->type !== $this->getValue('dashboard-type') &&
            $this->dashboard->id == $this->getValue('dashboard') &&
            $this->checkForPublicDashlet($this->getValue('dashboard'))) {
            Notification::error("Action denied, the dashboard has a public dashlet!");
        } else {
            if ($this->dashboard->type !== $this->getValue('dashboard-type') &&
                $this->dashboard->id == $this->getValue('dashboard') &&
                ! $this->checkForPublicDashlet($this->getValue('dashboard'))) {
                $this->updateDashboardTable($this->dashboard, $this->getValue('dashboard'));

                Notification::success("Dashboard type updated!");
            }

            $this->updateDashletTable($this->dashlet, $this->getValue('dashboard'));

            Notification::success('Dashlet updated');
        }
    }
}
