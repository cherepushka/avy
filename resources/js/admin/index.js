import React from "react";
import ReactDOM from "react-dom/client";
import CategoriesTree from "./components/CategoryTree";
import AntCss from "antd/dist/antd.compact.min.css";

document.adoptedStyleSheets = [AntCss];
const container = document.querySelector('.tree-container');

const treeContainer = ReactDOM.createRoot(container);

const inputsContainer = document.querySelector('.catalog-upload-inputs');
const categoriesInput = inputsContainer.querySelector('.category_ids_input');

treeContainer.render(<CategoriesTree categoriesInput={categoriesInput}/>);