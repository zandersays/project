<?php
// A sample task
$tasks = array(
    array(
        'name' => 'Task 1',
        'description' => 'This task does something.',
        'status' => 'active',
        'lastRun' => String::time('now -20 seconds'),
        'lastRunStatus' => 'success',
        'nextRun' => String::time('now +30 minutes'),
        'schedule' => 'Every Wednesday',
        'runCount' => '4',
    ),
);

if(empty($tasks)) {
    $taskScheduler = '<h2>No tasks found.</h2>';
}
else {
    $taskScheduler = '
        <p><a class="buttonLink plusSquareGrey" href="add-a-task/">Add a Task</a></p>
        <table>
            <thead>
                <tr>
                    <td class="statusColumn"></td>
                    <td>Name</td>
                    <td>Last Run</td>
                    <td>Next Run</td>
                    <td>Schedule</td>
                    <td>History</td>
                    <td></td>
                </tr>
            </thead>
    ';


    $tbody = new HtmlElement('tbody');
    if(!empty($tasks)) {
        foreach($tasks as $taskIndex => $task) {
            $tbody->append('
                <tr>
                    <td class="statusColumn"><a class="statusGood" title="Active"></a></td>
                    <td><a>'.$task['name'].'</a></td>
                    <td>'.String::title($task['lastRunStatus']).', '.Time::timeSinceString($task['lastRun']).' ago<br />'.String::date('F j, Y g:h:s A', $task['lastRun']).'</td>
                    <td>'.Time::timeToString($task['nextRun']).' away<br />'.String::date('F j, Y g:h:s A', $task['nextRun']).'</td>
                    <td>'.$task['schedule'].'</td>
                    <td><a>'.$task['runCount'].' times</a></td>
                    <td><a class="buttonLink clockBlueArrowGreen">Run Now</a></td>
                </tr>
            ');
        }
    }
    $taskScheduler .= $tbody;

    $taskScheduler .= '
        <tfoot>
            <tr>
                <td class="statusColumn"></td>
                <td>Name</td>
                <td>Last Run</td>
                <td>Next Run</td>
                <td>Schedule</td>
                <td>History</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    ';
}
?>