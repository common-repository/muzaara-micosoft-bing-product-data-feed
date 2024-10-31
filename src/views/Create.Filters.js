import React from "react"
import {getText, FieldsToSelect} from "../functions"

export default class Filters extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        let conditions = this.props.conditions.map( (condition, index) => {
            return (
                <option value={condition.condition} key={index}>{condition.name}</option>
            )
        })

        let filters = this.props.filters.map((filter, index) => {
            return (
                <tr key={index}>
                    <td>
                        <select value={filter.if} onChange={this.props.onUpdate.bind(this, index, "if")}>
                            <FieldsToSelect fields={this.props.fields} />
                        </select>
                    </td>
                    <td>
                        <select value={filter.condition} onChange={this.props.onUpdate.bind(this, index, "condition")} required>
                            <option value=""></option>
                            {conditions}
                        </select>
                    </td>
                    <td>
                        {filter.valueType == 0 ? (
                            <select value={filter.value} onChange={this.props.onUpdate.bind(this, index, "value")} required>
                                <FieldsToSelect fields={this.props.fields} exclude={filter.if} />
                            </select>
                        ) : (
                            <input type="text" value={filter.value} onChange={this.props.onUpdate.bind(this, index, "value")} required />
                        )}
                        
                    </td>
                    <td>
                        <input type="checkbox" value={filter.valueType} onChange={this.props.onUpdate.bind(this, index, "valueType")} checked={filter.valueType} />
                    </td>
                    <td>
                        <select value={filter.then} onChange={this.props.onUpdate.bind(this, index, "then")}>
                            <option value={1}>{getText("include")}</option>
                            <option value={0}>{getText("exclude")}</option>
                        </select>
                    </td>
                    <td>
                        {this.props.filters.length > 1 ? (
                            <span title={getText("remove")} className="dashicons dashicons-trash" style={{color: "#a00", cursor: "pointer"}} onClick={this.props.delete.bind(this, index)}></span>
                        ) : null }
                        
                    </td>
                </tr>
            )
        })
        return (
            <section className="muzaara-woopf-section">
                <div className="notice notice-info inline" style={{margin: 0, padding: "1px 20px"}}>
                        <h3>Things to know about Filters/Rules</h3>
                        <ul type="square" style={{listStyle: "disc outside none"}}>
                            <li>IN and NOT IN conditions accepts comma separated values</li>
                            <li>Product Date field uses the default date format in Settings > General</li>
                            <li>IS EMPTY & NOT EMPTY conditions ignores the value field when being processed</li>
                            <li>BETWEEN & NOT BETWEEN requires 2 numbers separated with comma. E.g. 50,100. Invalid input will ignore the rule/filter</li>
                        </ul>
                    
                </div>
                <h3>{getText("filters")}</h3>
                <table className="widefat">
                    <thead>
                        <tr>
                            <th className="row-title">{getText("if")}</th>
                            <th>{getText("condition")}</th>
                            <th>{getText("value")}</th>
                            <th>{getText("freeText")}</th>
                            <th>{getText("then")}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {filters}
                    </tbody>
                </table>
                <button type="button" className="button-secondary" onClick={this.props.addNew}>{getText("newFilter")}</button>
            </section>
        )
    }
}