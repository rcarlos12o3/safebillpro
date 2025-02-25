export const checkPermissionEditPrices = {
    computed:
    {
    },
    methods: 
    {
        hasPermissionEditItemPrices(permission_edit_item_prices)
        {
            if(permission_edit_item_prices != null && permission_edit_item_prices != undefined)
            {
                return permission_edit_item_prices
            }

            return true
        }
    }
}