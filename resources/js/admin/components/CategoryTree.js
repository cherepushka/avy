import React from "react";
import { Tree } from "antd";
import { useState } from "react";

function CategoriesTree({categoriesInput}) {
    const [checkedKeys, setCheckedKeys] = useState([]);

    const onCheck = (checkedKeysValue, event) => {
        const checkedKeys = checkedKeysValue.concat(event.halfCheckedKeys);
        setCheckedKeys(checkedKeysValue);
        categoriesInput.value = checkedKeys.join(',');
    };

    return (
        <Tree
            checkable
            treeData={treeData}
            checkedKeys={checkedKeys}
            onCheck={onCheck}
        />
    )
}

export default CategoriesTree