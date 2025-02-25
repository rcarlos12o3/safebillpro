import { checkPermissionEditPrices } from '@mixins/check-permission-edit-prices'

export const editableRowItems = {
    mixins: [
        checkPermissionEditPrices
    ],
    computed:
    {
        showEditableItems()
        {
            return this.configuration && this.configuration.change_values_preview_document
        },
        // canEditRowPrices()
        // {
        //     return (this.typeUser === 'admin') ? true : this.configuration.allow_edit_unit_price_to_seller
        // },
    },
    methods: 
    {
        hasRowAdvancedOption(row)
        {
            const has_discounts = row.discounts && Array.isArray(row.discounts) && row.discounts.length > 0
            const has_charges = row.charges && Array.isArray(row.charges) && row.charges.length > 0
            const has_isc = row.item.has_isc

            return has_discounts || has_charges || has_isc
        },
        setRowTotalsWithoutRounding(row, totals)
        {
            row.total_value_without_rounding = totals.total_value
            row.total_base_igv_without_rounding = totals.total_base_igv
            row.total_igv_without_rounding = totals.total_igv
            row.total_taxes_without_rounding = totals.total_taxes
            row.total_without_rounding = totals.total
        },
        setRowValuesFreeAffectationIgv(row, total_plastic_bag_taxes)
        {
            if (row.affectation_igv_type.free) 
            {
                row.price_type_id = '02'
                row.unit_value = 0
                row.total = 0 + total_plastic_bag_taxes
                row.total_without_rounding = 0
            }
        },
        setRowCalculatedTotals(row, totals)
        {
            row.total_base_igv = _.round(totals.total_base_igv, 2)
            row.total_igv = _.round(totals.total_igv, 2)
            row.total_taxes = _.round(totals.total_taxes, 2)

            if(totals.total_value) row.total_value = _.round(totals.total_value, 2)

            if(totals.total) row.total = _.round(totals.total, 2)
        },
        getRowCalculatedTotalIgv(affectation_igv_type_id, total_base_igv)
        {
            let total_igv = 0

            switch (affectation_igv_type_id) 
            {
                case '10':
                    total_igv = total_base_igv * this.percentage_igv
                    break
            
                case '20':
                case '30':
                    total_igv = 0
                    break
            }

            return total_igv
        },
        getRowCalculatedUnitValue(affectation_igv_type_id, unit_price)
        {
            let unit_value = 0

            switch (affectation_igv_type_id) 
            {
                case '10':
                    unit_value = parseFloat(unit_price) / (1 + this.percentage_igv)
                    break
            
                case '20':
                case '30':
                    unit_value = parseFloat(unit_price)
                    break
            }

            return unit_value
        },
        getRowCalculatedUnitPrice(affectation_igv_type_id, unit_value)
        {
            let unit_price = 0

            switch (affectation_igv_type_id) 
            {
                case '10':
                    unit_price = parseFloat(unit_value) * (1 + this.percentage_igv)
                    break
            
                case '20':
                case '30':
                    unit_price = parseFloat(unit_value)
                    break
            }

            return unit_price
        },
        getRowTotalPlasticBagTaxes(row)
        {
            let total_plastic_bag_taxes = 0

            if (row.input_has_plastic_bag_taxes) 
            {
                total_plastic_bag_taxes = _.round(row.quantity * row.item.amount_plastic_bag_taxes, 1)
                row.total_plastic_bag_taxes = total_plastic_bag_taxes
            }

            return total_plastic_bag_taxes
        },
        startRowTotalsVariables(row, input_total_value)
        {
            let total_value_partial = input_total_value
            let total_value = total_value_partial
            let total_base_igv = total_value_partial
            let total_igv = this.getRowCalculatedTotalIgv(row.affectation_igv_type_id, total_base_igv)

            let total_plastic_bag_taxes = this.getRowTotalPlasticBagTaxes(row)
            let total_taxes = total_igv + total_plastic_bag_taxes
            let total = total_value + total_taxes

            return { 
                total_value,
                total_base_igv,
                total_igv,
                total_taxes,
                total,
                total_plastic_bag_taxes
            }
        },
        setDifferentUnitPrice(row)
        {
            if(parseFloat(row.item.unit_price) != row.unit_price)
            {
                if(this.form.currency_type_id !== row.item.currency_type_id)
                {
                    if (row.item.currency_type_id === 'PEN' && this.form.currency_type_id === 'USD') 
                    {
                        row.item.unit_price = this.getFormatUnitPriceRow(row.unit_price * this.form.exchange_rate_sale)
                    }
                    else 
                    {
                        row.item.unit_price = this.getFormatUnitPriceRow(row.unit_price / this.form.exchange_rate_sale)
                    }

                    return
                }

                row.item.unit_price = this.getFormatUnitPriceRow(row.unit_price)
            }
        },
        endRowCalculateTotal(row, total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes)
        {
            row.item.has_editable_row_items = true
            this.setDifferentUnitPrice(row)
            this.setRowTotalsWithoutRounding(row, { total_value, total_base_igv, total_igv, total_taxes, total })
            this.setRowValuesFreeAffectationIgv(row, total_plastic_bag_taxes)
            this.calculateTotal()
        },
        recalculateRowTotals(row)
        {
            const { total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes } = this.startRowTotalsVariables(row, row.unit_value * row.quantity)

            this.setRowCalculatedTotals(row, { total_value, total_base_igv, total_igv, total_taxes, total })

            this.endRowCalculateTotal(row, total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes)
        },
        changeRowTotal(row)
        {
            let input_total = row.total
            if (row.input_has_plastic_bag_taxes) input_total -= row.total_plastic_bag_taxes 

            row.unit_price = parseFloat(input_total) / parseFloat(row.quantity)
            row.unit_value = this.getFormatUnitPriceRow(this.getRowCalculatedUnitValue(row.affectation_igv_type_id, row.unit_price))
            if(row.item.purchase_unit_price > row.unit_price){
                let decimal_input_unit_price_value = parseFloat(row.input_unit_price_value);
                row.unit_price = decimal_input_unit_price_value;
                this.changeRowUnitPrice(row);
                return this.$message.error('El precio de compra no puede ser superior al precio de venta');
            }

            const { total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes } = this.startRowTotalsVariables(row, row.unit_value * row.quantity)

            this.setRowCalculatedTotals(row, { total_value, total_base_igv, total_igv, total_taxes })
            
            this.endRowCalculateTotal(row, total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes)
        },
        changeRowTotalValue(row)
        {
            row.unit_value = this.getFormatUnitPriceRow(parseFloat(row.total_value) / parseFloat(row.quantity))
            row.unit_price = this.getRowCalculatedUnitPrice(row.affectation_igv_type_id, row.unit_value)

            if(row.item.purchase_unit_price > row.unit_price){
                let decimal_input_unit_price_value = parseFloat(row.input_unit_price_value);
                row.unit_price = decimal_input_unit_price_value;
                this.changeRowUnitPrice(row);
                return this.$message.error('El precio de compra no puede ser superior al precio de venta');
            }
            const { total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes } = this.startRowTotalsVariables(row, row.total_value)

            this.setRowCalculatedTotals(row, { total_base_igv, total_igv, total_taxes, total })

            this.endRowCalculateTotal(row, total_value, total_base_igv, total_igv, total_taxes, total, total_plastic_bag_taxes)
        },
        changeRowUnitValue(row)
        {
            row.unit_price = this.getRowCalculatedUnitPrice(row.affectation_igv_type_id, row.unit_value)
            if(row.item.purchase_unit_price > row.unit_price){
                let decimal_input_unit_price_value = parseFloat(row.input_unit_price_value);
                row.unit_price = decimal_input_unit_price_value;
                this.changeRowUnitPrice(row);
                return this.$message.error('El precio de compra no puede ser superior al precio de venta');
            }
            this.recalculateRowTotals(row)
        },
        changeRowUnitPrice(row)
        {
            row.unit_value = this.getFormatUnitPriceRow(this.getRowCalculatedUnitValue(row.affectation_igv_type_id, row.unit_price))
            if(row.item.purchase_unit_price > row.unit_price){
                let decimal_input_unit_price_value = parseFloat(row.input_unit_price_value);
                row.unit_price = decimal_input_unit_price_value;
                this.changeRowUnitPrice(row);
                return this.$message.error('El precio de compra no puede ser superior al precio de venta');
            }

            this.recalculateRowTotals(row)
        },
        changeRowQuantity(row)
        {
            this.recalculateRowTotals(row)
        },
    }

}