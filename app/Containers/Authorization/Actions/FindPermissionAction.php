<?php

namespace App\Containers\Authorization\Actions;

use Apiato\Core\Foundation\Facades\Apiato;
use App\Containers\Authorization\Exceptions\PermissionNotFoundException;
use App\Containers\Authorization\Models\Permission;
use App\Ship\Parents\Actions\Action;
use App\Ship\Parents\Transporters\Transporter;

/**
 * Class FindPermissionAction.
 *
 * @author Mahmoud Zalt <mahmoud@zalt.me>
 */
class FindPermissionAction extends Action
{

    /**
     * @param \App\Ship\Parents\Transporters\Transporter $data
     *
     * @return  \App\Containers\Authorization\Models\Permission
     */
    public function run(Transporter $data): Permission
    {
        $permission = Apiato::call('Authorization@FindPermissionTask', [$data->id]);

        if (!$permission) {
            throw new PermissionNotFoundException();
        }

        return $permission;
    }

}
