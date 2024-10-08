/**
 * Creates two column layout
 *
 * @param {HTMLElement} leftContent
 * @param {HTMLElement} rightContent
 *
 * @constructor
 */
const TwoColumnRowLayoutComponent = (leftContent, rightContent) => {
    const generator = Unzer.elementGenerator;

    const wrapper = generator.createElement("div", "unzer-two-row-layout-container");
    const columnLeft = generator.createElement("div", "unzer-two-row-layout-column");

    const columnRight = generator.createElement("div", "unzer-two-row-layout-column");
    
    columnLeft.appendChild(leftContent);
    columnRight.appendChild(rightContent);

    wrapper.appendChild(columnLeft);
    wrapper.appendChild(columnRight);

    return wrapper;

};


Unzer.components.TwoColumnRowLayout = {
    create: TwoColumnRowLayoutComponent
};
