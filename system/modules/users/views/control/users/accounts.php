<p><a class="buttonLink add" href="add-an-account/">Add an Account</a></p>
<p><input type="text" class="searchInput" /><a class="buttonLink search" type="submit">Search Accounts</a></p>

<table>
    <thead>
        <tr>
            <td class="userStatusColumn"></td>
            <td>Account Name</td>
            <td>Members</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="userStatusColumn"><a class="userOnline" title="Online"></a></td>
            <td><a>Project</a></td>
            <td><a>kirkouimet</a> (Owner)</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td class="userStatusColumn"></td>
            <td>Account Name</td>
            <td>Members</td>
        </tr>
    </tfoot>
</table>
<?php
    $pagination = ModelList::pagination(0, 50, 100, '/');
    echo $pagination;
?>