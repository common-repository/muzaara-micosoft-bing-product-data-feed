import React from "react"
import View from "./../views/Home"
import {getText, request} from "../functions"
import { withRouter } from "react-router-dom"

class Home extends React.Component {
    constructor(props) {
	super(props)

	this.state = {
	    isReady: parseInt(MUZAARA_WOOPF_BING.hasAccess),
	    channels: [],
	    gettingChannels: false
	}

	this.checkReady = this.checkReady.bind(this)
	this.acCreateNew = this.acCreateNew.bind(this)
	this.getChannels = this.getChannels.bind(this)
	this.pauseChannel = this.pauseChannel.bind(this)
	this.deleteChannel = this.deleteChannel.bind(this)
	this.runChannel = this.runChannel.bind(this)

	this.reloadInt = null;
    }

    componentDidMount() {
	this.getChannels()

	this.reloadInt = setInterval(this.getChannels, 15000)
    }

    componentWillUnmount() {
	clearInterval(this.reloadInt)
    }

    checkReady() {
	return request({
	    method: "POST",
	    body: { action: "muzaara_woopf_bing_checkLink" }
	})
	.then( response => response.json() )
	.then( json => {
	    this.setState({isReady: json.success})
	    return json.success
	})
    }

    deleteChannel(index, e) {
	if ( typeof(e) != "undefined" ) {
	    e.preventDefault()
	}

	if ( index < this.state.channels.length ) {
	    if ( confirm( getText("deleteConfirmation" ).replace( "%s", this.state.channels[index]["name"] ) ) ) {
		let id = this.state.channels[index].id
		let channels = this.state.channels.filter( (v, i) => i != index )
		
		this.setState({channels}, () => {
		    request({
			method: "POST",
			body: {action: "muzaara_woopf_bing_deleteFeed", feed_id: id}
		    })
		})
	    }
	}
    }

    pauseChannel(index,e ) {
	if ( typeof(e) != "undefined" ) {
	    e.preventDefault()
	}

	if ( index < this.state.channels.length ) {
	    let channels = [...this.state.channels]
	    channels[index].is_active = !channels[index].is_active 

	    this.setState({channels}, () => {
		request({
		    method: "POST",
		    body: {action: ( !this.state.channels[index].is_active ? "muzaara_woopf_bing_pauseFeed" : "muzaara_woopf_bing_resumeFeed" ), feed_id: channels[index]["id"] }
		})
	    })
	}
    }

    runChannel(index, e) {
	if ( typeof(e) != "undefined" ) {
	    e.preventDefault()
	}

	if ( index < this.state.channels.length ) {
	    let channels = [...this.state.channels]
	    channels[index].is_running = true 

	    this.setState({channels}, () => {
		request({
		    method: "POST",
		    body: { action: "muzaara_woopf_bing_runFeed", feed_id: channels[index][ "id" ] }
		})
		.then( response => response.json() )
		.then( json => {
		    let channels = [...this.state.channels]

		    channels[index].is_running = false
		    channels[index].dump_url = json.data

		    this.setState({channels})
		})
	    })
	}
    }

    getChannels() {
	this.setState({gettingChannels: true}, () => {
	    request({
		method: "POST",
		body: {action: "muzaara_woopf_bing_getFeeds"}
	    })
	    .then( response => response.json() )
	    .then( json => {
		if ( json.success ) {
		    this.setState({channels: json.data, gettingChannels: false})
		}
	    })
	})
    }

    acCreateNew() {
	this.props.history.push("/create")
    }

    render() {
	return <View ctrl={this} />
    }
}

export default withRouter(Home)