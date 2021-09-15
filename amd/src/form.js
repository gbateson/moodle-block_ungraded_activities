// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * blocks/ungraded_activities.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2021 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.9
 */

define(["core/str"], function(STR) {

    /** @alias module:block_ungraded_activities/view */
    var JS = {};

    // cache the plugin name and string cache
    JS.plugin = "block_ungraded_activities";
    JS.str = {};

    /*
     * initialize this AMD module
     */
    JS.init = function() {

        STR.get_strings([
            {"key": "deselectall",    "component": "moodle"},
            {"key": "selectall",      "component": "moodle"},
            {"key": "apply",          "component": JS.plugin},
            {"key": "exportsettings", "component": JS.plugin},
            {"key": "importsettings", "component": JS.plugin}
        ]).done(function(s) {
            var i = 0;
            JS.str.deselectall    = s[i++];
            JS.str.selectall      = s[i++];
            JS.str.apply          = s[i++];
            JS.str.exportsettings = s[i++];
            JS.str.importsettings = s[i++];

            var fieldset = document.querySelector("#id_activities");
            if (fieldset) {
                fieldset.querySelectorAll("[id$=showactivities] select").forEach(function(elm){
                    var x = elm;
                    while (x && ! x.matches(".fitem")) {
                        x = x.parentNode;
                    }
                    while (x && ! x.matches("div:empty")) {
                        x = x.previousElementSibling;
                    }
                    if (x) {
                        x.style.width = "4px";
                    }
                });
            }

            var elm = document.querySelector("select[name^=config_mycourses]");
            if (elm) {
                var fieldsets = "#id_title, #id_activities, #id_users, #id_date, #id_applyselectedvalues";
                document.querySelectorAll(fieldsets).forEach(function(fieldset){
                    fieldset.querySelectorAll(".row.fitem .col-md-9").forEach(function(elm, i){

                        var div = document.createElement("DIV");

                        var node = null;

                        // get name of parent element e.g. "fitem_id_description"
                        // and remove everything up to final underscore "_".
                        var name = elm.parentNode.id;
                        name = name.replace(new RegExp("^.+_"), "");

                        if (name == "description") {
                            elm.classList.remove("col-md-9");
                            elm.classList.add("col-md-7");

                            div.classList.add("d-none");
                            div.classList.add("d-md-block");
                            div.classList.add("col-md-2");
                            div.classList.add("px-0");
                            div.classList.add("text-right");

                            node = elm.querySelector(".exportsettings");
                            if (node) {
                                div.appendChild(node);
                            }

                            node = elm.querySelector(".importsettings");
                            if (node) {
                                div.appendChild(node);
                            }

                            var input = document.createElement("INPUT");
                            input.setAttribute("type", "checkbox");
                            input.setAttribute("name", "select_all");
                            input.setAttribute("id", "id_select_all");
                            input.classList.add("ml-2");
                            if (input.addEventListener) {
                                input.addEventListener("change", JS.onchange_selectall, false);
                            } else if (input.attachEvent) {
                                input.attachEvent("onchange", JS.onchange_selectall);
                            }

                            var label = document.createElement("LABEL");
                            label.setAttribute("for", "id_select_all");
                            label.classList.add("d-inline");
                            label.innerText = JS.str.selectall;

                            node = document.createElement("DIV");
                            node.classList.add("bg-secondary");
                            if (i == 0) {
                                node.classList.add("border-top");
                            }
                            node.classList.add("border-bottom");
                            node.classList.add("border-dark");
                            node.classList.add("w-100"); // width: 100%
                            node.classList.add("py-1");
                            node.classList.add("text-right");
                            node.style.paddingRight = "35px";
                            node.classList.add("itemselect");

                            node.appendChild(label);
                            node.appendChild(input);

                            div.appendChild(node);
                        } else {
                            elm.classList.remove("col-md-9");
                            elm.classList.add("col-md-8");

                            div.classList.add("d-none");
                            div.classList.add("d-md-block");
                            div.classList.add("col-md-1");
                            div.classList.add("bg-secondary");
                            if (i == 0) {
                                div.classList.add("border-top");
                            }
                            div.classList.add("border-bottom");
                            div.classList.add("border-dark");
                            div.classList.add("text-center");
                            div.classList.add("itemselect");

                            node = document.createElement("INPUT");
                            if (name == "mycourses") {
                                // Create button to "Apply" settings.
                                node.setAttribute("type", "submit");
                                node.setAttribute("value", JS.str.apply);
                                node.classList.add("btn");
                                node.classList.add("btn-primary");
                                node.classList.add("my-1");
                                node.classList.add("px-2");
                            } else {
                                // Create checkbox for this setting.
                                node.setAttribute("type", "checkbox");
                                node.setAttribute("name", "select_" + name);
                                node.setAttribute("id", "id_select_" + name);
                                node.classList.add("mt-2");
                            }

                            div.appendChild(node);
                        }

                        elm.parentNode.insertBefore(div, elm.nextSibling);
                    });
                });
            }
        });
    };

    JS.onchange_selectall = function(){
        var checked = this.checked;
        var label = this.parentNode.querySelector("label");
        if (label) {
            label.innerText = (checked ? JS.str.deselectall : JS.str.selectall);
        }
        var s = ".itemselect input[type=checkbox]:not([name=" + this.name + "])";
        document.querySelectorAll(s).forEach(function(checkbox){
            checkbox.checked = checked;
        });
    };

    return JS;
});