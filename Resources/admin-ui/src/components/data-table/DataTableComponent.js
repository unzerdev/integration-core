/**
 * @typedef TableHeader
 * @property {string} label
 * @property {string} code
 * @property {(item: Record<string, any>) => void?} valueProvider
 * @property {'text' | 'status' | 'money' | 'copyText' | 'date'?} [type='text']
 */

/**
 * @typedef TableRow
 * @property {Record<string, any>} item
 * @property {TableAction[]?} actions
 */

/**
 * @typedef TableAction
 * @property {string} label
 * @property {'regular' | 'destructive'?} type
 * @property {() => any} onClick
 */

/**
 * @typedef TableCell
 * @property {any} value
 * @property {string?} className
 * @property {(value: any) => HTMLElement?} renderer
 */

/**
 * @typedef FilterValue
 * @property {string} code
 * @property {string[]} value
 */

/**
 * @typedef DataTableModel
 * @property {TableHeader[]} header
 * @property {TableRow[]} rows
 * @property {TableFilterParams[]?} filters
 * @property {(page: number, filters: FilterValue[]) => Promise<{hasNext: boolean, data: TableRow[]}>} onDataNeeded
 * @property {string?} className
 * @property {string?} noItemsLabel
 */

const DataTable = () => {
    const generator = Unzer.elementGenerator;
    const components = Unzer.components;

    /** @type HTMLElement */
    let container;
    /** @type TableHeader[] */
    let headerData;
    /** @type Record<string, string[]> */
    let activeFilters = {};
    /** @type number */
    let page = 1;
    /** @type boolean */
    let nextPageAvailable = true;
    /** @type boolean */
    let currentlyLoading = false;

    /**
     * @param {TableAction[]} actions
     * @return {HTMLElement}
     */
    const renderActionsCell = (actions) => {
        return components.Button.createList(
            actions?.map((action) => ({
                type: 'ghost',
                className: action.type === 'destructive' ? 'adlm--destructive' : 'adlm--blue',
                label: action.label,
                onClick: action.onClick
            }))
        );
    };

    /**
     * @param {string} value
     * @return {HTMLElement}
     */
    const renderTextCell = (value) => {
        return generator.createElement('span', '', value);
    };

    /**
     * @param {string} value
     * @param {string} copyValue
     * @return {HTMLElement}
     */
    const renderCopyCell = (value, copyValue) => {
        const button = components.Button.create({
            type: 'ghost',
            size: 'small',
            className: 'unzer-copy-icon',
            onClick: () => {
                navigator.clipboard.writeText(copyValue).then(() => {
                    button.classList.add('adls--copied');
                    button.blur();
                    setTimeout(() => {
                        button.classList.remove('adls--copied');
                    }, 2000);
                });
            }
        });

        const hintWrapper = generator.createHint(button, 'dataTable.copy');

        return generator.createElement('span', 'unzer-copy-cell', value, null, [hintWrapper]);
    };

    /**
     * @param {Date} value
     * @return {HTMLElement}
     */
    const renderDateCell = (value) => {
        return generator.createElement('span', '', Unzer.utilities.formatDate(value));
    };

    /**
     * @param {string} value
     * @return {HTMLElement}
     */
    const renderStatusCell = (value) => {
        const val = value.toLowerCase();
        return generator.createElement('span', `unzer-status adlt--${val}`, `dataTable.status.${val}`);
    };

    /**
     * @param {Money} value
     * @return {HTMLElement}
     */
    const renderMoneyCell = (value) => {
        const amount = new Intl.NumberFormat('en-US').format(value.amount);

        return generator.createElement('span', '', `${amount} ${value.currency}`);
    };

    /**
     * Renders table cell.
     *
     * @param {TableCell} cellData
     * @returns {HTMLElement}
     */
    const renderCell = (cellData) => {
        return generator.createElement('td', cellData.className, '', null, [cellData.renderer(cellData.value)]);
    };

    /**
     * Gets the basic table element.
     *
     * @return {HTMLElement}
     */
    const getTableElement = () => {
        return generator.createElementFromHTML(
            '<div class="adl-table-wrapper"><table><thead><tr></tr></thead><tbody></tbody></table></div>'
        );
    };

    const loadRows = (handler, callback) => {
        showLoader();
        handler(
            page,
            Object.entries(activeFilters).map(([code, value]) => ({ code, value }))
        )
            .then(
                /** @param {{hasNext: boolean, data: TableRow[]}} response */
                (response) => {
                    nextPageAvailable = response.hasNext;
                    callback(response.data);
                }
            )
            .catch(console.error)
            .finally(hideLoader);
    };

    /**
     * Creates data table.
     *
     * @param {DataTableModel} params
     */
    const create = ({ header, rows, className = '', filters, onDataNeeded, noItemsLabel }) => {
        container = generator.createElement('div', `adl-data-table ${className}`);
        headerData = header;

        if (rows.length === 0) {
            container.append(createNoItemsMessage(noItemsLabel || 'dataTable.noItems'));

            return container;
        }

        if (filters?.length) {
            container.append(
                renderTableFilter(filters, () => {
                    page = 1;
                    replaceRows([]);
                    loadRows(onDataNeeded, replaceRows);
                })
            );
        }

        const tableWrapper = getTableElement();
        container.append(tableWrapper);
        tableWrapper?.addEventListener('scroll', () => {
            if (
                nextPageAvailable &&
                !currentlyLoading &&
                tableWrapper.scrollTop + tableWrapper.clientHeight > tableWrapper.scrollHeight - 10
            ) {
                page++;
                loadRows(onDataNeeded, appendRows);
            }
        });

        const heading = container.querySelector('table thead tr');

        header.forEach((header) => {
            heading.append(generator.createElement('th', header.className, header.label));
        });

        heading.append(generator.createElement('th', 'unzer-actions', 'dataTable.actions'));

        appendRows(rows);

        return container;
    };

    /**
     * Appends table rows.
     *
     * @param {TableRow[]} items
     */
    const appendRows = (items) => {
        items.length &&
        container.querySelector('table tbody').append(
            ...items.map((row) => {
                const rowElem = generator.createElement('tr');
                rowElem.append(
                    ...Object.entries(row.item).reduce((result, entry) => {
                        const [code, value] = entry;
                        const header = headerData.find((h) => h.code === code);

                        if (!header) {
                            return result;
                        }

                        switch (header.type) {
                            case 'copyText':
                                return [
                                    ...result,
                                    renderCell({
                                        value,
                                        renderer: (value) => renderCopyCell(
                                            value,
                                            header.valueProvider?.(row.item) || value
                                        )
                                    })
                                ];
                            case 'date':
                                return [...result, renderCell({ value, renderer: renderDateCell })];
                            case 'money':
                                return [...result, renderCell({ value, renderer: renderMoneyCell })];
                            case 'status':
                                return [...result, renderCell({ value, renderer: renderStatusCell })];
                            default:
                                return [...result, renderCell({ value, renderer: renderTextCell })];
                        }
                    }, [])
                );

                rowElem.append(
                    renderCell({
                        value: row.actions,
                        renderer: renderActionsCell,
                        className: 'unzer-actions'
                    })
                );

                return rowElem;
            })
        );
    };

    /**
     * Replaces table rows.
     *
     * @param {TableRow[]} items
     */
    const replaceRows = (items) => {
        Unzer.pageService.clearComponent(container.querySelector('table tbody'));
        appendRows(items);
    };

    /**
     * Renders the empty list message with the image.
     *
     * @param {string} label
     * @returns {HTMLElement}
     */
    const createNoItemsMessage = (label) => {
        return generator.createElement('div', 'unzer-no-items-wrapper', '', null, [
            generator.createElementFromHTML(
                '<svg width="213" height="213" viewBox="0 0 213 213" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' +
                '<rect width="213" height="213" fill="url(#pattern0)"/>' +
                '<defs>' +
                '<pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">' +
                '<use xlink:href="#image0_71_19302" transform="scale(0.00469484)"/>' +
                '</pattern>' +
                '<image id="image0_71_19302" width="213" height="213" xlink:href="data:image/png;base64,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' +
                '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' +
                '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' +
                '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"/>' +
                '</defs>' +
                '</svg>'
            ),
            generator.createElement('p', '', label)
        ]);
    };

    /**
     * Creates table filers.
     *
     * @param {TableFilterParams[]} filtersConfig
     * @param {() => void} onChange
     */
    const renderTableFilter = (filtersConfig, onChange) => {
        const filterComponents = [];

        const createButton = Unzer.components.Button.create;
        let filtersContainer = container.querySelector('.unzer-table-filter-wrapper');
        let filters;
        if (!filtersContainer) {
            filters = generator.createElement('div', 'unzer-table-filters');
            filtersContainer = generator.createElement('div', 'unzer-table-filter-wrapper', '', null, [
                createButton({
                    type: 'ghost',
                    size: 'medium',
                    className: 'adlm--blue unzer-filters-switch-button',
                    label: 'dataTable.filter',
                    onClick: () => {
                        filtersContainer.classList.toggle('adls--filters-active');
                    }
                }),
                filters
            ]);
        } else {
            filters = filtersContainer.querySelector('.unzer-table-filters');
            Unzer.pageService.clearComponent(filters);
        }

        const changeFilter = (filter, values) => {
            activeFilters[filter] = values;
            resetButton.disabled =
                Object.values(activeFilters).reduce((result, options) => result + options.length, 0) === 0;
            onChange();
        };

        const resetButton = createButton({
            type: 'ghost',
            label: 'dataTable.resetAll',
            size: 'small',
            className: 'unzer-reset-button',
            disabled: true,
            onClick: () => {
                activeFilters = {};
                filterComponents.forEach((filter) => filter.reset());

                onChange();
                resetButton.disabled = true;
            }
        });

        filtersConfig.forEach((filter) => {
            filter.onChange = (values) => changeFilter(filter.name, values);
            filterComponents.push(components.TableFilter(filter));
        });

        filters.append(...filterComponents.map((filter) => filter.create()), resetButton);

        return filtersContainer;
    };

    const showLoader = () => {
        currentlyLoading = true;
        container.querySelector('.unzer-table-filters')?.classList.add('adls--disabled');
        if (!container.querySelector('.adl-table-wrapper .adl-loader')) {
            container.querySelector('.adl-table-wrapper').append(generator.createLoader({ type: 'small' }));
        }
    };

    const hideLoader = () => {
        currentlyLoading = false;
        container.querySelector('.unzer-table-filters')?.classList.remove('adls--disabled');
        container.querySelector('.adl-table-wrapper .adl-loader')?.remove();
    };

    return {
        create,
        appendRows,
        replaceRows
    };
};

Unzer.components.DataTable = DataTable;
