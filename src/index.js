import React from "react"
import {render} from "react-dom"
import {HashRouter as Router, Switch, Route} from "react-router-dom"
import Home from "./controller/Home"
import Channel from "./controller/Channel"

const App = () => {
    return (
        <Router>
            <Switch>
                <Route path="/create">
                    <Channel />
                </Route>
                <Route path="/edit/:id">
                    <Channel />
                </Route>
                <Route path="/">
                    <Home />
                </Route>
            </Switch>
        </Router>
    )
}

render(<App />, document.getElementById( "muzaara-woopf-bing" ) );
