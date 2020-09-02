# README #


### What is this repository for? ###


* Summary
    The purpose of this plugin is to provide and integrate solution for 
    adding admin menus, submenus, help tabs and help sidebars. To leverage this 
    subsystem you need to inject the descriptive details of the component you 
    want to create (admin menu, submenu, help tab or help sidebar). The 
    subsystem takes care of interacting with WordPress to ensure your 
    component shows as expected.
    The Logic class fulfills the purpose of the subsystem. In addition it:
        - incorporates action hooks to facilitate external changes
        - injects a help tab into the controller's "data visualization" submenu
          providing descriptive details about the subsystem.

    The Api class provides methods for external interaction with the subsystem. 
    the relevant details (title, content, etc.) to describe your addition. 
* Version
    Release: 1.0.0
* License GPL 2+
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
* Repo owner
    https://twocarrs.com/dblox
    support@twocarrs.com
