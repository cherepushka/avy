import React from "react";
import ReactDOM from "react-dom/client";
import CategoriesTree from "./components/admin/CategoryTree";
import AntCss from "antd/dist/antd.compact.min.css";

document.adoptedStyleSheets = [AntCss];

document.querySelectorAll('.tree-container').forEach(container => {
    const treeContainer = ReactDOM.createRoot(container);
    treeContainer.render(<CategoriesTree/>);
})