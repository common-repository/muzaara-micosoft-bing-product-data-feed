import React from "react"
import Create from "../views/Create"
import { withRouter } from "react-router-dom"
import {getText, request} from "../functions"

class Channel extends React.Component {
	constructor(props) {
		super(props)

		this.newFilterRule = {
			if : "id",
			condition: "",
			value: "",
			then: 0,
			valueType: 0,
			ifFieldType: 1,
			valueFieldType: 1
		}

		this.newRule = {
			if: "",
			condition: "",
			value: "",
			then : "",
			is: "",
			isFieldType: 1,
			isType: 0,
			ifFieldType: 1,
			thenFieldType: 1,
			valueType: 0,
			valueFieldType: 1
		}

		this.defaultFields = {
			id: typeof( this.props.match.params.id ) != "undefined" ? parseInt(this.props.match.params.id) : null,
			name: "",
			country: "",
			pushType: 2,
			refreshRate: 24,
			productTypes: ["simple"],
			merchantId: "",
			catalogId: '',
			noticeEmail: "",
			mappings: [
				{ productField: "name", gField : "title", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "id", gField : "offer_id", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "description", gField : "description", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "link", gField : "link", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "image", gField : "image_link", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "price", gField : "price", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "stock_status", gField : "availability", prefix: "", suffix: "", type: 0, productFieldType: 1 },
				{ productField: "", gField : "condition", prefix: "", suffix: "", type: 0, productFieldType: 1 },
			],
			categoryMappings: {},
			filters: [], // [Object.assign({}, this.newFilterRule)],
			rules: [],
			utm: {
				utm_source: "",
				utm_medium: "",
				utm_term: "",
				utm_content: "",
				utm_campaign: ""
			}
		}

		this.state = {
			channel: Object.assign({}, this.defaultFields),
			creatingLoading: false,
			creationStep: 1,
			fields : [],
			gfields : [],
			categories: [],
			gcatSearch: {},
			activeCatMap: null,
			filterConditions: [],
			errors: [],
			productTypes: {},
			creating: false,
			gettingChannel : false
		}

		this.acCancelNew                =   this.acCancelNew.bind(this)
		this.getFields                  =   this.getFields.bind(this)
		this.updateSimpleFields         =   this.updateSimpleFields.bind(this)
		this.creationNextStep           =   this.creationNextStep.bind(this)
		this.updateMapping              =   this.updateMapping.bind(this)
		this.deleteMapping              =   this.deleteMapping.bind(this)
		this.newMapping                 =   this.newMapping.bind(this)
		this.updateCategoryMapping      =   this.updateCategoryMapping.bind(this)
		this.selectAutocompleteResult   =   this.selectAutocompleteResult.bind(this)
		this.updateFilterRules          =   this.updateFilterRules.bind(this)
		this.addNewFilter               =   this.addNewFilter.bind(this)
		this.deleteFilter               =   this.deleteFilter.bind(this)
		this.addNewRule                 =   this.addNewRule.bind(this)
		this.updateRule                 =   this.updateRule.bind(this)
		this.deleteRule                 =   this.deleteRule.bind(this)
		this.goBack                     =   this.goBack.bind(this)
		this.updateUtm                  =   this.updateUtm.bind(this)
		this.createChannel              =   this.createChannel.bind(this)
		this.getProductTypes            =   this.getProductTypes.bind(this)
		this.addProductType             =   this.addProductType.bind(this)
		this.getChannel                 =   this.getChannel.bind(this)
		this.passDownCategory           =   this.passDownCategory.bind(this)

		document.body.onclick = (e) => {
			if ( e.target.nodeName != "LI" ) {
				this.setState({activeCatMap: null})
			}
		}
	}

	componentDidMount() {
		this.getChannel()
		this.getFields()
		this.getCategories()
		this.getConditions()
		this.getProductTypes()
	}

	getChannel() {
		if ( this.state.channel.id ) {
			this.setState({ gettingChannel: true}, () => {
				request({
					method: "POST",
					body: { action: "muzaara_woopf_bing_getFeed", feed_id: this.state.channel.id }
				})
				.then( response => response.json() )
				.then( json => {
					if ( json.success ) {
						this.setState({ channel: Object.assign(this.state.channel, json.data)})
					}
				})
			})
		}
	}

	getProductTypes() {
		this.setState({creatingLoading: true}, () => {
			request({
				method: "POST",
				body: {action: "muzaara_woopf_bing_getProductTypes"}
			})
			.then( response => response.json() )
			.then( json => {
				this.setState({productTypes: json.data, creatingLoading: false})
			})
		})
		
	}

	getConditions() {
		request({
			method: "POST",
			body: {action: "muzaara_woopf_bing_getConditions" }
		})
		.then( response => response.json() )
		.then( json => {
			this.setState({filterConditions: json.data.filter})
		})
	}

	createChannel( e ) {
		e.preventDefault()
		this.setState({creating: true, errors: []}, () => {
			request({
				method: "POST",
				body: Object.assign({action: "muzaara_woopf_bing_createChannel"}, this.state.channel )
			})
			.then( response => response.json() )
			.then( json => {
				if ( json.success ) {
					this.props.history.push( "/" )
				} else {
					let errors = [{id: "create_error", message: json.data}]
					this.setState({errors})
				}
			})
		})
		
	}

	addProductType(key) {
		if ( typeof( this.state.productTypes[key] ) != "undefined" ) {
			let channel = Object.assign({}, this.state.channel)
			if ( !this.state.channel.productTypes.includes(key) )
				channel.productTypes = [...channel.productTypes, key]
			else 
				channel.productTypes = channel.productTypes.filter( v => v != key )

			this.setState({channel, creatingLoading: ( channel.productTypes.length < 1 )})
		}
	}

	updateUtm(key, e) {
		if (typeof(this.state.channel.utm[key]) != "undefined") {
			let channel = Object.assign({}, this.state.channel)
			channel.utm[key] = e.target.value 

			this.setState({channel})
		} 
	}

	updateMapping(index, field, e) {
		if (index < this.state.channel.mappings.length ) {
			let channel = Object.assign({}, this.state.channel)

			let mappings = [...channel.mappings]
			let map = Object.assign({}, mappings[index])
			let isCheckbox = e.target.type == "checkbox"
			let value = isCheckbox ? e.target.checked : e.target.value

			if ( isCheckbox )
				map.productField = ""

			map[field] = value 
			if ( field == "productField" ) {
				map["productFieldType"] = parseInt(typeof(e.target.selectedOptions) != "undefined" ? e.target.selectedOptions[0].dataset.fieldType : 0 )
			}
			mappings[index] = map
			channel.mappings = mappings

			this.setState({ channel });
		}
	} 

	passDownCategory( start, value, isparent ) {
		let categoryMappings = Object.assign({}, this.state.channel.categoryMappings)

		if ( !isparent && start < this.state.categories.length ) {
			for( let i = start; i < this.state.categories.length; i++ ) {
				categoryMappings[this.state.categories[i].id] = value
			}
		} else {
			categoryMappings = Object.assign(categoryMappings, this.setChildren(start, value) );
			
		}

		let channel = Object.assign({}, this.state.channel)
		channel.categoryMappings = categoryMappings
		this.setState({channel})
	}

	setChildren(parent, value) {
		let map = {}

		let categories = this.state.categories.filter( cat => cat.parent == parent )
		if ( !categories.length ) 
			return {}

		for( let cat of categories ) {
			map[cat.id] = value
			map = Object.assign(map, this.setChildren(cat.id, value) );
		}

		return map;
	}

	updateCategoryMapping(id, e) {
		let channel = Object.assign({}, this.state.channel )

		channel.categoryMappings[id] = e.target.value 
		if ( channel.categoryMappings[id].trim().length >= 3 || !isNaN(channel.categoryMappings[id] ) ) {
			if (typeof(this.searchInt) != "undefined")
				clearTimeout(this.searchInt)

			this.searchInt = setTimeout(() => {
				this.setState({ gcatSearch: {0: getText( "searching" )}, activeCatMap: id}, () => {
					request({
						method: "POST", 
						body: {action: "muzaara_woopf_bing_searchCat", cat_q: channel.categoryMappings[id].trim()}
					})
					.then( response => response.json() )
					.then( json => {
						let gcatSearch = {}
						if ( json.success ) {
							gcatSearch = json.data 
						}
	
						this.setState({gcatSearch, activeCatMap: id})
					})
				})
				
			}, 500);
		}
		this.setState({channel})
	}
	
	addNewRule() {
		let channel = Object.assign({}, this.state.channel)
		channel.rules = [...channel.rules, Object.assign({}, this.newRule)]

		this.setState({channel})
	}

	deleteRule(index) {
		if (this.state.channel.rules.length > index) {
			let channel = Object.assign({}, this.state.channel)
			channel.rules = channel.rules.filter( ( rule, key ) => key != index )

			this.setState({channel})
		}
	}

	updateRule(index, prop, e) {
		let channel = Object.assign({}, this.state.channel)
		let rules = [...channel.rules]
		let target = e.target 
		let value = target.type == "checkbox" ? target.checked : target.value

		if ( index < rules.length && typeof(rules[index][prop]) != "undefined" ) {
			if ( typeof(rules[index][`${prop}FieldType`]) != "undefined" ) {
				rules[index][`${prop}FieldType`] = parseInt( typeof(target.selectedOptions) == "undefined" ? 1 : target.selectedOptions[0].dataset.fieldType )
			}

			rules[index][prop] = value
			if ( prop == "isType" ) {
				rules[index]["is"] = ""
			}

			if ( prop == "valueType" ) {
				rules[index]["value"] = ""
			}
		}
		
		this.setState({channel})
	}

	addNewFilter() {
		let channel = Object.assign({}, this.state.channel)
		channel.filters = [...channel.filters, Object.assign({}, this.newFilterRule)]

		this.setState({channel})
	}

	deleteFilter(i) {
		let channel = Object.assign({}, this.state.channel)
		channel.filters = channel.filters.filter( (filter, index ) => index != i )
		this.setState({channel})
	}

	updateFilterRules(index, prop, e) {
		let channel = Object.assign({}, this.state.channel)
		let filters = [...channel.filters]
		let fieldType = parseInt( typeof(e.target.selectedOptions) == "undefined" ? 1 : e.target.selectedOptions[0].dataset.fieldType )
		let value = e.target.value

		if ( index < filters.length && typeof(filters[index][prop]) != "undefined" ) {
			if ( prop == "valueType" ) {
				value = e.target.checked
				filters[index]["value"] = ""
			}

			filters[index][prop] = value
			if ( prop == "if" ) 
				filters[index]["ifFieldType"] = fieldType

			if ( prop == "value" && filters[index][ "valueType" ] != 0 ) {
				filters[index]["valueFieldType"] = fieldType
			}

			channel.filters = filters
		}

		this.setState({channel})
	}

	goBack() {
		if ( this.state.creationStep > 1 ) {
			this.setState({creationStep: this.state.creationStep-1})
		}
	}

	selectAutocompleteResult(index) {
		if ( typeof( this.state.gcatSearch[index]) != "undefined" && this.state.activeCatMap !== null && index != 0 ) {
			let channel = Object.assign({}, this.state.channel)
			channel.categoryMappings[this.state.activeCatMap] = this.state.gcatSearch[index]
			this.setState({channel, activeCatMap: null, gcatSearch: {}})
		}
	}

	deleteMapping(index) {
		let channel = Object.assign({}, this.state.channel)
		channel.mappings = channel.mappings.filter( (_, i) => i != index )

		this.setState({ channel })
	}

	newMapping() {
		let channel = Object.assign({}, this.state.channel)
		let n = {
			productField: "",
			gField: "",
			prefix: "",
			suffix: "",
			type: 0
		}
		channel.mappings = [...channel.mappings, n]

		this.setState({channel})
	}

	updateSimpleFields(prop, e) {
		if (typeof(this.state.channel[prop]) !== "undefined" ) {
			let channel = Object.assign({}, this.state.channel)
			let value = e.target.value
			channel[prop] = value 
			this.setState({channel})
		}
	}

	creationNextStep( e ) {
		e.preventDefault()

		this.setState({errors : []}, () => {
			let creationStep = this.state.creationStep
			let errors = []

			if ( this.state.creationStep == 1 && this.state.channel.name.trim() != "" && this.state.channel.country.trim() != "" ) {
				creationStep++ 
			} else if ( this.state.creationStep == 2 ) {
				let emptyMaps = this.state.channel.mappings.filter(map => map.productField.trim() == "" )
				let emptyFilters = this.state.channel.filters.filter( filter => filter.value.trim() == "" )
				let emptyRules = this.state.channel.rules.filter( rule => rule.if.trim() == "" || rule.then.trim() == "" )
	
				if ( emptyRules.length > 0 ) {
					errors.push({
						id: "rules",
						message: getText( "errorCheckRules" )
					})
				}
	
				if ( emptyFilters.length > 0 ) {
					errors.push({
						id: "filters",
						message: getText("errorCheckFilters")
					})
				}
	
				if ( emptyMaps.length > 0 ) {
					errors.push({
						id: "maps",
						message: getText("errorCheckMaps")
					})
				}
	
				if ( errors.length == 0 )
					creationStep++
			} else if( this.state.creationStep == 3 ) {
				creationStep++
			}

			this.setState({creationStep, errors})
		})
	}

	getFields(t) {
		this.setState({creatingLoading: true}, () => {
			request({
				method: "POST",
				body: {action: "muzaara_woopf_bing_getProductFields"}
			})
			.then( response => response.json() )
			.then( json => {
				this.setState({creatingLoading: false, fields: json.data.product, gfields: json.data.google }, (typeof(t) == "function" ? t : null ))
			})
		})
	}

	getCategories() {
		request({
			method: "POST",
			body: {action: "muzaara_woopf_bing_getProductCategories"}
		})
		.then(response => response.json() )
		.then( json => {
			let channel = Object.assign({}, this.state.channel)
			for( let cat of json.data ) {
				if ( typeof(channel.categoryMappings[cat.id] ) == "undefined" ) 
					channel.categoryMappings[cat.id] = ""
			}

			this.setState({categories: json.data, channel})
		})
	}

	acCancelNew() {
		this.props.history.push("/")
	}

	render() {
		return <Create ctrl={this} />
	}
}

export default withRouter(Channel)