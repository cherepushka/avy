import React from "react";
import ReactDOM from "react-dom/client";
import CategoriesTree from "./components/CategoryTree";
import AntCss from "antd/dist/antd.compact.min.css";

document.adoptedStyleSheets = [AntCss];

document.querySelectorAll('.tree-container').forEach(container => {
    const treeContainer = ReactDOM.createRoot(container);

    const inputsContainer = container.parentNode.querySelector('.catalog-upload-inputs');
    const categoriesInput = inputsContainer.querySelector('.category_ids_input');

    treeContainer.render(<CategoriesTree categoriesInput={categoriesInput}/>);
})