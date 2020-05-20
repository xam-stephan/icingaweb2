<?php

namespace Icinga\Module\Dashboards\Form;

use Icinga\Authentication\Auth;
use Icinga\Exception\SystemPermissionException;
use Icinga\Module\Dashboards\Common\Database;
use ipl\Html\Html;
use ipl\Web\Compat\CompatForm;

class DeleteDashboardForm extends CompatForm
{
    use Database;

    /** @var object $dashboard single dashboard from the database */
    protected $dashboard;

    /**
     * Create a dashboard delete Form
     *
     * @param object $dashboard  The dashboard that is deleted
     */
    public function __construct($dashboard)
    {
        $this->dashboard = $dashboard;
    }

    protected function assemble()
    {
        $this->add(
            Html::tag(
                'h1',
                null,
                Html::sprintf(
                    'Please confirm deletion of dashboard \'%s\'',
                    $this->dashboard->name
                )
            )
        );

        $this->addElement('input', 'btn_submit', [
            'type' => 'submit',
            'value' => 'Confirm Removal',
            'class' => 'btn-primary autofocus'
        ]);
    }

    protected function onSuccess()
    {
        $user = Auth::getInstance()->getUser()->getUsername();

        if ($this->dashboard->type === 'system' && ! Auth::getInstance()->getUser()->isMemberOf('admin')) {
            throw new SystemPermissionException("You don't have a permission to delete public dashboards!");
        } elseif (Auth::getInstance()->getUser()->isMemberOf('admin')) {
            $this->getDb()->delete('dashlet', ['dashboard_id = ?' => $this->dashboard->id]);
            $this->getDb()->delete('dashboard', ['id = ?' => $this->dashboard->id]);
        } elseif ($this->dashboard->type === 'private' && $this->dashboard->owner === $user) {
            $this->getDb()->delete('dashlet', ['dashboard_id = ?' => $this->dashboard->id]);
            $this->getDb()->delete('dashboard', ['id = ?' => $this->dashboard->id]);
        }
    }
}
