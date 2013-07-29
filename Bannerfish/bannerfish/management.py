# = Bannerfish =
# Copyright (c) 2002 Itamar Shtull-Trauring, all rights reserved.
# 
# This application is free software; you can redistribute it and/or
# modify it under the terms of version 2 of the GNU General Public
# License as published by the Free Software Foundation.
# 
# This application is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this application; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

"""Web-based management interface."""

from twisted.web import widgets
from twisted.persisted import styles


class Page(widgets.WidgetPage):
    """Standard template for management pages."""

    template = '''
    <html>
    <head>
    <title>%%%%self.title%%%%</title>
    <base href="%%%%request.prePathURL()%%%%">
    </head>
    <body>
    <p>
    <a href=".">View all ads</a> || <a href="create">Create a new ad</a> ||
    <a href="config">Configure</a>
    </p>
    %%%%self.widget%%%%
    </body>
    </html>
    '''


class EditForm(widgets.Form):
    """Edit a banner."""

    title = "Edit Ad"

    def __init__(self, service):
        self.service = service

    def getFormFields(self, request, fieldSet=None):
        banner = self.service.banners[request.args['name'][0]]
        sections = banner.sections
        c = self.service.config
        return widgets.Form.getFormFields(self, request, fieldSet=[
            ['hidden', 'Banner %s' % banner.getId(), 'name', banner.getId()],
            ['string', 'Title: ', 'title', banner.title],
            ['text', 'Description:', 'description', banner.description],
            ['string', 'URL: ', 'URL', banner.URL],
            ['int', 'Width:', 'width', banner.width],
            ['int', 'Height:', 'height', banner.height],
            ['int', 'Paid Views:', 'paidViews', banner.paidViews],
            ['float', 'Weight:', 'weight', banner.weight],
            ['string', 'Password:', 'password', banner.password],
            ['checkbox', 'Enabled:', 'enabled', banner.enabled],
            ['file', 'Image:', 'file', None],
            ['checkgroup', 'Sections:', 'sections', [(s, s, s in sections) for s in c.sections]],
            ])

    def process(self, write, request, submit, **kwargs):
        banner = self.service.banners[kwargs['name']]
        banner.update(kwargs)
        write("Changed saved.")


class CreateNewForm(widgets.Form):
    """Create new banner."""

    title = "Create New Ad"

    def __init__(self, service):
        self.service = service
    
    def getFormFields(self, request, fieldSet=None):
        sections = self.service.config.sections
        return widgets.Form.getFormFields(self, request, fieldSet=[
            ['string', 'Banner ID:', 'name', ''],
            ['string', 'Title: ', 'title', ''],
            ['text', 'Description:', 'description', ''],
            ['string', 'URL: ', 'URL', ''],
            ['int', 'Width:', 'width', 100],
            ['int', 'Height:', 'height', 100],
            ['int', 'Paid Views:', 'paidViews', 10000],
            ['float', 'Weight:', 'weight', 1],
            ['string', 'Password:', 'password', ''],
            ['checkbox', 'Enabled:', 'enabled', 0],
            ['file', 'Image:', 'file', None],
            ['checkgroup', 'Sections:', 'sections', [(s, s, 1) for s in sections]],
            ])

    def process(self, write, request, submit, **kwargs):
        if not kwargs.has_key('name') or not kwargs['name'].isalnum():
            raise ValueError, "please choose better name"
        banner = self.service.banners.createEntity(kwargs['name'])
        banner.views = 0
        banner.clicks = 0
        banner.update(kwargs)
        write("New banner created.")


class Management(styles.Versioned, widgets.StreamWidget, widgets.Gadget):
    """Management gadget."""

    title = "Bannerfish Management"
    pageFactory = Page

    # persistence upgrades
    persistenceVersion = 2
    
    def upgradeToVersion2(self):
        self.putWidget('config', EditConfigForm(self.service))
    
    def __init__(self, service):
        widgets.Gadget.__init__(self)
        self.service = service
        self.putWidget('create', CreateNewForm(service))
        self.putWidget('config', EditConfigForm(service))
        self.putWidget('edit', EditForm(service))
        
    def stream(self, write, request):
        write("<ul>")
        for name, banner in self.service.banners.items():
            write('<li><a href="edit?name=%s">%s (%s)</a></li>\n' % (name, name, banner.title))
        write("</ul>")



class EditConfigForm(widgets.Form):
    """Edit configuration."""

    title = "Configure"

    def __init__(self, service):
        self.service = service

    def getFormFields(self, request, fieldSet=None):
        c = self.service.config
        return widgets.Form.getFormFields(self, request, fieldSet=[
            ['checkgroup', 'Remove Sections: ', 'remove', [(s, s, 0) for s in c.sections]],
            ['string', 'Add New Section: ', 'new', ''],
            ])

    def process(self, write, request, submit, remove=None, new=None):
        sections = list(self.service.config.sections)
        if remove:
            for s in remove: sections.remove(s)
        if new:
            sections.append(new)
        self.service.config.sections = sections
        write("Changed saved.")
