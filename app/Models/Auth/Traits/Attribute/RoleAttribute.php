<?php

namespace App\Models\Auth\Traits\Attribute;

/**
 * Trait RoleAttribute.
 */
trait RoleAttribute
{
    /**
     * @return string
     */
    public function getEditButtonAttribute()
    {
        return '<a href="'.route('admin.auth.role.edit', $this).'" class="cbtn csuccess btn-icon p-2"><i class="text-white mdi mdi-pencil menu-icon" data-toggle="tooltip" data-placement="top" title="'.__('buttons.general.crud.edit').'"></i></a>';
    }

    /**
     * @return string
     */
    public function getDeleteButtonAttribute()
    {
        return '<button type="button" class="cbtn cdanger btn-icon p-1 delete_modal_1" data-url="'.route('admin.auth.role.destroy', $this).'"  data-toggle="modal" data-target="#delete_role_modal" value="Delete"><i class="mdi mdi-delete"></i></button>';
        // return '<a href="'.route('admin.auth.role.destroy', $this).'"
		// 	 data-method="delete"
		// 	 data-trans-button-cancel="'.__('buttons.general.cancel').'"
		// 	 data-trans-button-confirm="'.__('buttons.general.crud.delete').'"
		// 	 data-trans-title="'.__('strings.backend.general.are_you_sure').'"
		// 	 class="cbtn cdanger btn-icon p-2"><i class="text-white mdi mdi-delete" data-toggle="tooltip" data-placement="top" title="'.__('buttons.general.crud.delete').'"></i></a> ';
    }

    /**
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        if ($this->id == 1) {
            return 'N/A';
        }

        return '<div class="btn-group btn-group-sm" role="group" aria-label="'.__('labels.backend.access.users.user_actions').'">
			  '.$this->edit_button.'
			  '.$this->delete_button.'
			</div>';
    }
}
