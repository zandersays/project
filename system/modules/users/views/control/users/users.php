<?php
    echo '<p><a class="buttonLink plusSquareGrey" href="add-a-user/">Add a User</a></p>';
    /*
    echo '
        <p>
            <input type="text" class="searchInput" value="'.$usersSearch.'" />
            <a class="buttonLink magnifyingGlass" type="submit">Search Users</a>
        </p>
    ';
    */
?>
<table>
    <thead>
        <tr>
            <td class="statusColumn"></td>
            <td>Username</td>
            <!--<td>Accounts</td>-->
            <td>Status</td>
            <td>Created</td>
        </tr>
    </thead>
    <tbody>
        <?php

            foreach($userList as $user) {
                echo '<tr>';
                echo '<td class="statusColumn"><a class="statusGood" title="Online"></a></td>';
                echo '<td><a href="edit-user/userId:'.$user->getId().'/">'.$user->getUsername().'</a></td>';
                echo '<td>'.$user->getStatus().'</td>';
                echo '<td>'.Time::timeSinceString($user->getTimeAdded()).' ago</td>';
                echo '</tr>';
            }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td class="statusColumn"></td>
            <td>Username</td>
            <!--<td>E-mail</td>
            <td>Accounts</td>-->
            <td>Status</td>
            <td>Created</td>
        </tr>
    </tfoot>
</table>
<?php
    $pagination = HtmlElement::pagination($usersStartOffset, $usersPerPage, $userCount, Project::getInstanceAccessPath().'project/modules/users/users/offset:[offset]/');
    echo $pagination;
?>