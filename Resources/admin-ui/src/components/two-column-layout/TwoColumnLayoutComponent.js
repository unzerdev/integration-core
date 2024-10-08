/**
 * Creates two column layout
 *
 * @param {HTMLElement[]} leftContent
 * @param {HTMLElement[]} rightContent
 *
 * @constructor
 */
const TwoColumnLayoutComponent = (leftContent, rightContent) => {
    const generator = Unzer.elementGenerator;

    const wrapper = generator.createElement("div", "adl-two-layout-container");
    const columnLeft = generator.createElement("div", "adl-two-layout-column");
    columnLeft.classList.add("unzer-two-layout-column-left");

    const columnRight = generator.createElement("div", "adl-two-layout-column");
    columnRight.classList.add("unzer-two-layout-column-right");

    leftContent.forEach(element => {
        columnLeft.appendChild(element);
    })

    rightContent.forEach(element => {
        columnRight.appendChild(element);
    })

    wrapper.appendChild(columnLeft);
    wrapper.appendChild(columnRight);

    return wrapper;

};

const TwoColumnLayoutComponentUpdateLeft = (page, leftContent) => {
    const left = document.querySelector('.unzer-two-layout-column-left');
    left.innerHTML = ""
    
    leftContent.forEach(element => {
        left.appendChild(element);
    })
}

const TwoColumnLayoutComponentUpdateRight = (page, rightContent) => {
    const right = document.querySelector('.unzer-two-layout-column-right');
    right.innerHTML = ""
    
    rightContent.forEach(element => {
        right.appendChild(element);
    })
}

Unzer.components.TwoColumnLayout = {
    create: TwoColumnLayoutComponent,
    updateLeft: TwoColumnLayoutComponentUpdateLeft,
    updateRight: TwoColumnLayoutComponentUpdateRight
};
