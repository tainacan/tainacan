context('create textArea-type fields tests', function(){
  beforeEach(() => {
    cy.loginByUI()
  })

  it('clear DB', function(){
    cy.clearDB()
  })

  it('create collection for create fields', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.contains('New Collection').click()
    cy.get('#tainacan-text-name').type('Book Fields')
    cy.get('#tainacan-text-description').type('Descrição book Fields')
    cy.get('#tainacan-select-status').select('Publish').should('have.value', 'publish')
    cy.get('#button-submit-collection-creation').click()
    cy.get('#primary-menu > .menu > .menu-header > .menu-list > li > .router-link-active > .icon > .mdi').click()
    cy.get('.b-table').should('contain', 'Book Fields')
  })

  it('canceled create textArea field public', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name canceled')
    cy.get('.textarea').type('name book canceled description')
    cy.get('#tainacan-select-status-publish > .check').click()
    cy.get(':nth-child(1) > .button').click()
    cy.get('.active-fields-area >').should('not.contain', 'TextArea name canceled')
  })

  it('create textArea-type field public', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name public')
    cy.get('.textarea').type('name book description')
    cy.get('#tainacan-select-status-publish > .check').click()
    cy.get(':nth-child(2) > .button').click()
    cy.get('.active-fields-area >').should('contain', 'TextArea name public')
  })

  it('create textArea-type field private', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name private')
    cy.get('.textarea').type('name book description')
    cy.get('#tainacan-select-status-private > .check').click()
    cy.get(':nth-child(2) > .button').click()
    cy.get('.active-fields-area >').should('contain', 'TextArea name private')
  })

  it('create textArea-type field public required', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name public required')
    cy.get('.textarea').type('name book description required')
    cy.get('#tainacan-select-status-publish > .check').click()
    cy.get(':nth-child(2) > .b-checkbox > .check').click()
    cy.get(':nth-child(2) > .button').click()
    cy.get('.active-fields-area >').should('contain', 'TextArea name public required')
  })

  it('create textArea-type field public multiple values', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name public multiple values')
    cy.get('.textarea').type('name book description multiple values')
    cy.get('#tainacan-select-status-publish > .check').click()
    cy.get(':nth-child(3) > .b-checkbox > .check').click()
    cy.get(':nth-child(2) > .button').click()
    cy.get('.active-fields-area >').should('contain', 'TextArea name public multiple values')
  })

  it('create textArea-type field public unique values', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.field > :nth-child(2) > :nth-child(2)').click()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').clear()
    cy.get('#fieldEditForm > :nth-child(1) > .control > .input').type('TextArea name public unique values')
    cy.get('.textarea').type('name book description multiple values')
    cy.get('#tainacan-select-status-publish > .check').click()
    cy.get(':nth-child(4) > .b-checkbox > .check').click()
    cy.get(':nth-child(2) > .button').click()
    cy.get('.active-fields-area >').should('contain', 'TextArea name public unique values')
  })

  it('check if fields are updated to page', function(){
    cy.visit('/wp-admin/admin.php?page=tainacan_admin#/collections')
    cy.get('h1').should('contain', 'Collections Page')
    cy.get('[data-label="Name"] > :nth-child(1) > .clickable-row').click()
    cy.get(':nth-child(4) > .router-link-active').should('contain', 'Items')
    cy.get('.menu > :nth-child(2) > :nth-child(5) > a').click()
    cy.get('h1').should('contain', 'Collection Fields Edition Page')
    cy.get('.active-fields-area >').should('not.contain', 'TextArea name canceled')
    cy.get('.active-fields-area >').should('contain', 'TextArea name public')
    cy.get('.active-fields-area >').should('contain', 'TextArea name private')
    cy.get('.active-fields-area >').should('contain', 'TextArea name public required')
    cy.get('.active-fields-area >').should('contain', 'TextArea name public multiple values')
    cy.get('.active-fields-area >').should('contain', 'TextArea name public unique values')
  })
})
