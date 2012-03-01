<p><a class="buttonLink add" href="add-an-account-type/">Add an Account Type</a></p>

<table>
    <thead>
        <tr>
            <td>Name</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><a>Project</a></td>
        </tr>
        <tr>
            <td><a>RentScore</a></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>Name</td>
        </tr>
    </tfoot>
</table>
<?php
    $pagination = ModelList::pagination(0, 50, 100, '/');
    echo $pagination;
?>