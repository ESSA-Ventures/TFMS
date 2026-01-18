<x-cards.notification :notification="$notification" :link="route('tasks.index').'?approval_status=pending'" :image="user()->image_url"
    title="Task Awaiting Approval" :text="$notification->data['heading']" :time="$notification->created_at" />
