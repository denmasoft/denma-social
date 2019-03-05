function User(data)
{
    if (!!data) {
        angular.extend(this, data);
    };
}