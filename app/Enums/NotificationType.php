<?php

namespace App\Enums;

enum NotificationType: string
{
    case TASK_CREATED = 'task.created';
    case TASK_STATUS_CHANGED = 'task.status_changed';
    case TASK_ASSIGNED = 'task.assigned';
    case TASK_UNASSIGNED = 'task.unassigned';
    case TASK_UPDATED = 'task.updated';

    case MEMBER_ADDED = 'member.added';
    case MEMBER_REMOVED = 'member.removed';

    case COMMENT_CREATED = 'comment.created';
}
