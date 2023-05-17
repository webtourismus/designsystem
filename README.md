Webtourismus Design System
=========================

Introduction
------------
This is a base module used as a direct dependency by other internal Webtourismus
modules "backend" and "frontend". It contains various admin and frontend
improvement tailored for Webtourismus customers.

This module is meant as a common base for all projects and targets admin and
frontend themes. It lives in the contrib space of a project.

The module "backend" is a custom, project-specific module depending on this
module. "backend" lives in the custom space of a project. It is mostly a
companion module for the "frontend" theme.

The theme "frontend" is a custom, project-specific theme depending on "backend",
also living in the custom space. It should be handled like a starterkit theme.
