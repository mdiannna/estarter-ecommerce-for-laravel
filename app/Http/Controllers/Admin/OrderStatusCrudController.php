<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\OrderStatusRequest as StoreRequest;
use App\Http\Requests\OrderStatusRequest as UpdateRequest;
use App\Models\OrderStatus;
use App\Mail\OrderStatusUpdate;

class OrderStatusCrudController extends CrudController
{

    public function setUp()
    {

        /*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
        $this->crud->setModel("App\Models\OrderStatus");
        $this->crud->setRoute("admin/order-statuses");
        $this->crud->setEntityNameStrings('order status', 'order statuses');

        /*
        |--------------------------------------------------------------------------
        | COLUMNS
        |--------------------------------------------------------------------------
        */
        $this->crud->addColumns([
            [
                'name'  => 'name',
                'label' => trans('order.status_name'),
            ],
            [
               'name'       => 'notification_template_id',
               'label'      => trans('notificationtemplates.notification_template'),
               'type'       => 'select2',
               'entity'     => 'notificationTemplate',
               'attribute'  => 'name',
               'model'      => "App\Models\NotificationTemplate",
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |-------------------------------------------------------------------------
        */
        $this->setPermissions();

        /*
        |--------------------------------------------------------------------------
        | FIELDS
        |--------------------------------------------------------------------------
        */
        $this->setFields();

        /*
        |--------------------------------------------------------------------------
        | AJAX TABLE VIEW
        |--------------------------------------------------------------------------
        */
        $this->crud->enableAjaxTable();

    }

    public function setPermissions()
    {
        // Get authenticated user
        $user = auth()->user();

        // Deny all accesses
        $this->crud->denyAccess(['list', 'create', 'update', 'delete']);

        // Allow list access
        if ($user->can('list_order_statuses')) {
            $this->crud->allowAccess('list');
        }

        // Allow create access
        if ($user->can('create_order_status')) {
            $this->crud->allowAccess('create');
        }

        // Allow update access
        if ($user->can('update_order_status')) {
            $this->crud->allowAccess('update');
        }

        // Allow delete access
        if ($user->can('delete_order_status')) {
            $this->crud->allowAccess('delete');
        }
    }

    public function setFields()
    {
        $this->crud->addFields([
            [
                'name'  => 'name',
                'label' => trans('order.status_name'),
                'type'  => 'text',
            ],
            [
               'name'       => 'notification_template_id',
               'label'      => trans('notificationtemplates.notification_template'),
               'type'       => 'select2',
               'entity'     => 'notificationTemplate',
               'attribute'  => 'name',
               'model'      => "App\Models\NotificationTemplate",
            ],
        ]);
    }




	public function store(StoreRequest $request)
	{
        $redirect_location = parent::storeCrud();

        return $redirect_location;
	}

	public function update(UpdateRequest $request)
	{
        
        $redirect_location = parent::updateCrud();

        $orderStatus = $this->crud->entry;

        return $redirect_location;


	}
}
