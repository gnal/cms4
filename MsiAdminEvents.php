<?php

namespace Msi\AdminBundle;

final class MsiAdminEvents
{
    const ENTITY_NEW_INIT = 'msi_admin.entity.new.init';

    const ENTITY_EDIT_INIT = 'msi_admin.entity.edit.init';

    const ENTITY_EDIT_COMPLETED = 'msi_admin.entity.edit.completed';

    const ENTITY_DELETE_INIT = 'msi_admin.entity.delete.init';

    const ENTITY_TOGGLE_INIT = 'msi_admin.entity.toggle.init';

    const ENTITY_TOGGLE_COMPLETED = 'msi_admin.entity.toggle.completed';
}
